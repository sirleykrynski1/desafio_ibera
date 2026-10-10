<?php

use App\Models\AlertaClimatica;
use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    config(['services.climate.url' => 'http://climate.test']);
});

function pronosticoProgramado(): array
{
    $datos = json_decode(file_get_contents(base_path('tests/Fixtures/climate-alert.json')), true);
    $datos['consultado_en'] = now()->utc()->toIso8601String();
    $datos['periodo']['desde'] = now()->toDateString();
    $datos['periodo']['hasta'] = now()->addDays(2)->toDateString();

    return $datos;
}

test('proceso guarda una alerta por hotel y ciclo sin duplicarla en reintentos', function () {
    $this->travelTo(now()->setTime(12, 0));
    $hotel = Establecimiento::factory()->create(['latitud' => -28.54, 'longitud' => -57.17]);
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response(pronosticoProgramado())]);
    $this->artisan('clima:actualizar')->assertExitCode(0);
    $this->artisan('clima:actualizar')->assertExitCode(0);
    $this->assertDatabaseCount('alerta_climatica', 1);
    $alerta = AlertaClimatica::firstOrFail();
    expect($alerta->establecimiento_id)->toBe($hotel->id);
    expect($alerta->milimetros_lluvia)->toBe('52.40');
    expect($alerta->detalle['version_reglas'])->toBe('clima-v1');
    Http::assertSentCount(2);
    $this->travel(6)->hours();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response(pronosticoProgramado())]);
    $this->artisan('clima:actualizar')->assertExitCode(0);
    $this->assertDatabaseCount('alerta_climatica', 2);
    Http::assertSentCount(1);
});

test('sin alerta no crea registros', function () {
    Establecimiento::factory()->create(['latitud' => -28.54, 'longitud' => -57.17]);
    $datos = pronosticoProgramado();
    $datos['alerta'] = ['activa' => false, 'codigo' => null, 'motivos' => []];
    $datos['precipitacion_acumulada_mm'] = 0;
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($datos)]);
    $this->artisan('clima:actualizar')->assertExitCode(0);
    $this->assertDatabaseCount('alerta_climatica', 0);
    Http::assertSentCount(1);
});

test('fallo de un hotel no impide consultar el siguiente', function () {
    Establecimiento::factory()->count(2)->create(['latitud' => -28.54, 'longitud' => -57.17]);
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::sequence()->push([], 503)->push(pronosticoProgramado())]);
    $this->artisan('clima:actualizar')->assertExitCode(1);
    $this->assertDatabaseCount('alerta_climatica', 1);
    Http::assertSentCount(2);
});

test('rechaza pronósticos antiguos y no elimina el historial', function () {
    $hotel = Establecimiento::factory()->create(['latitud' => -28.54, 'longitud' => -57.17]);
    AlertaClimatica::create(['establecimiento_id' => $hotel->id, 'fecha_evento' => '2026-01-01', 'tipo' => 'lluvia']);
    $datos = pronosticoProgramado();
    $datos['consultado_en'] = now()->subHours(7)->toIso8601String();
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($datos)]);
    $this->artisan('clima:actualizar')->assertExitCode(1);
    $this->assertDatabaseCount('alerta_climatica', 1);
    Http::assertSentCount(1);
});

test('coordenadas inválidas no consultan Python', function () {
    Establecimiento::factory()->create(['latitud' => 100]);
    Http::preventStrayRequests();
    $this->artisan('clima:actualizar')->assertExitCode(1);
    Http::assertNothingSent();
});

test('gobierno filtra y revisa alertas sin aceptar autor enviado por el cliente', function (string $rol) {
    $hotel = Establecimiento::factory()->create(['nombre' => 'Hotel con alerta']);
    $alerta = AlertaClimatica::create(['establecimiento_id' => $hotel->id, 'fecha_evento' => '2026-10-08', 'tipo' => '<script>lluvia</script>']);
    $inspector = User::factory()->create(['rol' => $rol]);
    $this->actingAs($inspector)->get(route('alertas.indice'))->assertOk()->assertSee('Hotel con alerta')->assertSee('&lt;script&gt;', false);
    $this->patch(route('alertas.revisar', $alerta), ['revisada_por' => $hotel->user_id])->assertRedirect(route('alertas.indice'));
    expect($alerta->fresh()->revisada_por)->toBe($inspector->id);
    expect($alerta->fresh()->revisada_en)->not->toBeNull();
    $this->get(route('alertas.indice'))->assertDontSee('Hotel con alerta');
    $this->get(route('alertas.indice', ['estado' => 'revisadas']))->assertSee('Hotel con alerta');
    $this->get(route('alertas.indice', ['estado' => 'inventado']))->assertSessionHasErrors('estado');
})->with(['inspector', 'admin_gobierno']);

test('alertas de gobierno requieren sesión y rol autorizado', function () {
    $hotel = Establecimiento::factory()->create();
    $alerta = AlertaClimatica::create(['establecimiento_id' => $hotel->id, 'fecha_evento' => '2026-10-08', 'tipo' => 'lluvia']);
    $this->get(route('alertas.indice'))->assertRedirect(route('login'));
    $this->actingAs($hotel->user)->get(route('alertas.indice'))->assertForbidden();
    $this->patch(route('alertas.revisar', $alerta))->assertForbidden();
    expect($alerta->fresh()->revisada_en)->toBeNull();
});

test('programación climática se ejecuta cada seis horas UTC sin superposición', function () {
    $eventos = collect(app(Schedule::class)->events())->filter(fn ($evento) => str_contains($evento->command ?? '', 'clima:actualizar'));
    expect($eventos)->toHaveCount(1);
    expect($eventos->first()->expression)->toBe('0 */6 * * *');
    expect($eventos->first()->timezone)->toBe('UTC');
    expect($eventos->first()->withoutOverlapping)->toBeTrue();
});
