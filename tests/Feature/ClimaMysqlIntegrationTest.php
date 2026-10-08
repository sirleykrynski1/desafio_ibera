<?php

use App\Jobs\ActualizarAlertaClimatica;
use App\Models\AlertaClimatica;
use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('MySQL conserva detecciones únicas y permite su revisión en gestión', function () {
    if (! getenv('CLIMATE_MYSQL_TEST_DATABASE')) {
        $this->markTestSkipped('Prueba optativa: indicar CLIMATE_MYSQL_TEST_DATABASE para una base local migrada.');
    }
    $this->withoutVite();
    config([
        'database.default' => 'mysql',
        'database.connections.mysql.database' => getenv('CLIMATE_MYSQL_TEST_DATABASE'),
        'database.connections.mysql.url' => null,
        'services.climate.url' => 'http://climate.test',
    ]);
    DB::purge('mysql');
    $conexion = DB::connection('mysql');
    $antes = $conexion->table('alerta_climatica')->count();
    $conexion->beginTransaction();
    try {
        $hotel = Establecimiento::factory()->create(['nombre' => 'PRUEBA TRANSACCIONAL CLIMA', 'latitud' => -28.54, 'longitud' => -57.17]);
        $inspector = User::factory()->create(['rol' => 'inspector']);
        $datos = json_decode(file_get_contents(base_path('tests/Fixtures/climate-alert.json')), true);
        $datos['consultado_en'] = now()->utc()->toIso8601String();
        Http::preventStrayRequests();
        Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($datos)]);
        expect(ActualizarAlertaClimatica::dispatchSync($hotel->id, 'prueba-mysql'))->toBeTrue();
        expect(ActualizarAlertaClimatica::dispatchSync($hotel->id, 'prueba-mysql'))->toBeTrue();
        $alerta = AlertaClimatica::where('establecimiento_id', $hotel->id)->sole();
        expect($alerta->detalle['alerta']['activa'])->toBeTrue();
        expect($alerta->milimetros_lluvia)->toBe('52.40');
        $this->actingAs($inspector)->get(route('alertas.indice'))->assertOk()->assertSee($hotel->nombre);
        $this->patch(route('alertas.revisar', $alerta))->assertRedirect(route('alertas.indice'));
        expect($alerta->fresh()->revisada_por)->toBe($inspector->id);
        Http::assertSentCount(2);
    } finally {
        $conexion->rollBack();
    }
    expect($conexion->table('alerta_climatica')->count())->toBe($antes);
});

test('consulta Python real desde un establecimiento temporal de MySQL', function () {
    if (! getenv('CLIMATE_MYSQL_TEST_DATABASE') || getenv('CLIMATE_LIVE_API_TEST') !== '1') {
        $this->markTestSkipped('Requiere MySQL local y autorización explícita de consulta real a la API.');
    }
    config([
        'database.default' => 'mysql',
        'database.connections.mysql.database' => getenv('CLIMATE_MYSQL_TEST_DATABASE'),
        'database.connections.mysql.url' => null,
    ]);
    DB::purge('mysql');
    $conexion = DB::connection('mysql');
    $conexion->beginTransaction();
    try {
        $hotel = Establecimiento::factory()->create(['latitud' => -28.54, 'longitud' => -57.17]);
        expect(ActualizarAlertaClimatica::dispatchSync($hotel->id, 'prueba-api-real'))->toBeTrue();
        $alerta = AlertaClimatica::where('establecimiento_id', $hotel->id)->first();
        if ($alerta !== null) {
            expect($alerta->detalle['alerta']['activa'])->toBeTrue();
            expect($alerta->detalle['ubicacion']['latitud'])->toBe(-28.54);
        }
    } finally {
        $conexion->rollBack();
    }
});
