<?php

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->withoutVite();
    config(['services.climate.url' => 'http://climate.test']);
});

function informeParaRevision(string $estado = 'evaluado'): AnalisisLaboratorio
{
    return AnalisisLaboratorio::create([
        'establecimiento_id' => Establecimiento::factory()->create()->id,
        'laboratorio' => 'Laboratorio de prueba', 'fecha_muestra' => '2026-10-08',
        'resultado_final' => 'Pendiente', 'resultado_sugerido' => 'alerta', 'estado' => $estado,
    ]);
}

test('gobierno registra dictamen auditable sin modificar evaluación automática ni permitir sobrescritura', function (string $rol, string $decision, string $estado) {
    $this->freezeTime();
    $informe = informeParaRevision($estado);
    $inspector = User::factory()->create(['rol' => $rol]);
    $this->actingAs($inspector)->post(route('analisis.revision', $informe), [
        'resultado_final' => $decision, 'observaciones_revision' => '<script>Fundamento revisado</script>',
        'revisado_por' => $informe->establecimiento->user_id, 'resultado_sugerido' => 'cumple',
    ])->assertRedirect(route('analisis.mostrar', $informe));
    $informe->refresh();
    expect($informe->resultado_final)->toBe($decision);
    expect($informe->resultado_sugerido)->toBe('alerta');
    expect($informe->estado)->toBe('revisado');
    expect($informe->revisado_por)->toBe($inspector->id);
    expect($informe->revisado_en->toDateTimeString())->toBe(now()->toDateTimeString());
    $this->get(route('analisis.mostrar', $informe))->assertOk()->assertSee('&lt;script&gt;Fundamento revisado&lt;/script&gt;', false);
    $this->get(route('gestion'))->assertDontSee($informe->establecimiento->nombre);
    $this->post(route('analisis.revision', $informe), ['resultado_final' => 'Rechazado', 'observaciones_revision' => 'Segundo envío'])
        ->assertSessionHasErrors('resultado_final');
    expect($informe->fresh()->resultado_final)->toBe($decision);
    expect($informe->fresh()->observaciones_revision)->toBe('<script>Fundamento revisado</script>');
})->with([['inspector', 'Aprobado', 'evaluado'], ['admin_gobierno', 'Rechazado', 'observado']]);

test('propietario no puede emitir dictamen ni entrar a gestión', function () {
    $informe = informeParaRevision();
    $this->actingAs($informe->establecimiento->user)->post(route('analisis.revision', $informe), [
        'resultado_final' => 'Aprobado', 'observaciones_revision' => 'Autorización inventada',
    ])->assertForbidden();
    $this->get(route('gestion'))->assertForbidden();
    expect($informe->fresh()->resultado_final)->toBe('Pendiente');
});

test('dictamen exige decisión válida y fundamento', function (array $datos, string $campo) {
    $informe = informeParaRevision();
    $this->actingAs(User::factory()->create(['rol' => 'inspector']))->post(route('analisis.revision', $informe), $datos)
        ->assertSessionHasErrors($campo);
    expect($informe->fresh()->resultado_final)->toBe('Pendiente');
})->with([
    [['resultado_final' => 'verde', 'observaciones_revision' => 'Texto'], 'resultado_final'],
    [['resultado_final' => 'Aprobado', 'observaciones_revision' => '   '], 'observaciones_revision'],
    [['resultado_final' => 'Aprobado', 'observaciones_revision' => str_repeat('a', 2001)], 'observaciones_revision'],
]);

test('no permite revisar mientras la lectura sigue pendiente', function () {
    $informe = informeParaRevision('pendiente_lectura');
    $this->actingAs(User::factory()->create(['rol' => 'inspector']))->post(route('analisis.revision', $informe), [
        'resultado_final' => 'Aprobado', 'observaciones_revision' => 'Texto',
    ])->assertSessionHasErrors('resultado_final');
    expect($informe->fresh()->estado)->toBe('pendiente_lectura');
});

test('login dirige según el rol', function (string $rol, string $destino) {
    $user = User::factory()->create(['rol' => $rol]);
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route($destino));
})->with([['inspector', 'gestion'], ['admin_gobierno', 'gestion'], ['propietario', 'dashboard']]);

test('dashboard muestra el panel del establecimiento', function () {
    $this->actingAs(User::factory()->create(['rol' => 'propietario']))
        ->get(route('dashboard'))->assertOk()->assertSee('Resumen del Establecimiento');
});

test('visitantes no acceden a revisión ni pronóstico', function () {
    $informe = informeParaRevision();
    $this->post(route('analisis.revision', $informe))->assertRedirect(route('login'));
    $this->get(route('establecimientos.clima', $informe->establecimiento))->assertRedirect(route('login'));
});

test('clima presenta la respuesta Python sin modificar dictámenes', function (bool $activa) {
    $informe = informeParaRevision();
    $hotel = $informe->establecimiento;
    $hotel->update(['latitud' => -28.54, 'longitud' => -57.17]);
    $payload = json_decode(file_get_contents(base_path('tests/Fixtures/climate-alert.json')), true);
    if (! $activa) {
        $payload['alerta'] = ['activa' => false, 'codigo' => null, 'motivos' => []];
        $payload['precipitacion_acumulada_mm'] = 0;
    }
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($payload)]);
    $this->actingAs($hotel->user)->get(route('establecimientos.clima', $hotel))->assertOk()
        ->assertSee($activa ? 'Alerta por precipitaciones' : 'Sin alerta de precipitaciones');
    Http::assertSent(fn ($request) => $request['latitud'] === -28.54 && $request['longitud'] === -57.17);
    expect($informe->fresh()->resultado_final)->toBe('Pendiente');
})->with([true, false]);

test('fallo de API no se presenta como ausencia de alerta', function () {
    $hotel = Establecimiento::factory()->create(['latitud' => -28.54, 'longitud' => -57.17]);
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::failedConnection()]);
    $this->actingAs($hotel->user)->get(route('establecimientos.clima', $hotel))->assertOk()
        ->assertSee('Pronóstico no disponible')->assertDontSee('Sin alerta de precipitaciones');
    Http::assertSentCount(1);
});

test('coordenadas inválidas y hotel ajeno no provocan consultas externas', function () {
    $hotel = Establecimiento::factory()->create(['latitud' => 100, 'longitud' => -57]);
    Http::preventStrayRequests();
    $this->actingAs($hotel->user)->get(route('establecimientos.clima', $hotel))->assertOk()->assertSee('Faltan coordenadas');
    $this->actingAs(User::factory()->create())->get(route('establecimientos.clima', $hotel))->assertNotFound();
    Http::assertNothingSent();
});

test('PDF original requiere permiso y una ruta dentro de almacenamiento de análisis', function () {
    Storage::fake('local');
    $informe = informeParaRevision();
    Storage::disk('local')->put('analisis/prueba.pdf', '%PDF-1.4 prueba');
    $informe->update(['ruta_pdf' => Storage::disk('local')->path('analisis/prueba.pdf')]);
    $this->actingAs($informe->establecimiento->user)->get(route('analisis.pdf', $informe))->assertDownload('analisis-'.$informe->getKey().'.pdf');
    $this->actingAs(User::factory()->create())->get(route('analisis.pdf', $informe))->assertNotFound();
    $informe->update(['ruta_pdf' => base_path('composer.json')]);
    $this->actingAs(User::factory()->create(['rol' => 'inspector']))->get(route('analisis.pdf', $informe))->assertNotFound();
});
