<?php

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

$lecturasAdministrativas = [
    '/admin/dashboard',
    '/admin/detalle-establecimientos',
    '/admin/alertas',
    '/admin/inspectores',
    '/admin/exportar',
    '/admin/establecimientos/1',
];

dataset('lecturas administrativas', $lecturasAdministrativas);

dataset('lecturas de gobierno', [
    ...$lecturasAdministrativas,
    '/icaa/dashboard',
    '/icaa/inspectores',
    '/icaa/alertas',
    '/icaa/exportar',
]);

test('las lecturas de gobierno requieren iniciar sesión', function (string $ruta): void {
    $this->get($ruta)->assertRedirect(route('login'));
})->with('lecturas de gobierno');

test('un propietario recibe 403 al consultar los paneles o exportar datos de gobierno', function (string $ruta): void {
    $this->actingAs(User::factory()->propietario()->create(['name' => 'Propietario de prueba']))
        ->get($ruta)->assertForbidden();
})->with('lecturas de gobierno');

test('un inspector recibe 403 al consultar rutas exclusivas de administración', function (string $ruta): void {
    $this->actingAs(User::factory()->inspector()->create(['name' => 'Inspector de prueba']))
        ->get($ruta)->assertForbidden();
})->with('lecturas administrativas');

test('las operaciones administrativas sin permiso no alteran establecimientos', function (string $rol, string $operacion): void {
    $propietario = User::factory()->propietario()->create(['name' => 'Propietario de prueba']);
    $hotel = Establecimiento::factory()->for($propietario)->create(['nombre' => 'Hotel conservado']);
    $estadoOriginal = $hotel->refresh()->getAttributes();

    if ($rol === 'propietario') {
        $this->actingAs($propietario);
    } elseif ($rol === 'inspector') {
        $this->actingAs(User::factory()->inspector()->create(['name' => 'Inspector de prueba']));
    }

    $ruta = match ($operacion) {
        'crear' => '/admin/establecimientos',
        'editar' => '/admin/establecimientos/'.$hotel->id,
        'eliminar' => '/admin/establecimientos/'.$hotel->id.'/eliminar',
    };

    $respuesta = $this->post($ruta, [
        'nombre' => 'Cambio no autorizado',
        'rubro' => 'hotel',
        'ubicacion' => 'Corrientes',
        'capacidad_maxima' => 20,
        'capacidad_biodigestor' => 3000,
        'tipo_destino_vuelco' => 'cursos_agua',
    ]);

    if ($rol === 'visitante') {
        $respuesta->assertRedirect(route('login'));
    } else {
        $respuesta->assertForbidden();
    }

    $this->assertDatabaseCount('establecimientos', 1);
    expect($hotel->fresh()?->getAttributes())->toBe($estadoOriginal);
})->with(['visitante', 'propietario', 'inspector'])->with(['crear', 'editar', 'eliminar']);

test('los roles autorizados pueden consultar el panel correspondiente', function (string $rol, string $prefijo, string $pagina, string $vista): void {
    $usuario = $rol === 'admin_gobierno'
        ? User::factory()->gobierno()->create(['name' => 'Administrador de prueba'])
        : User::factory()->inspector()->create(['name' => 'Inspector de prueba']);

    $this->actingAs($usuario)->get('/'.$prefijo.'/'.$pagina)
        ->assertOk()->assertViewIs($vista);
})->with([
    ['admin_gobierno', 'admin'],
    ['admin_gobierno', 'icaa'],
    ['inspector', 'icaa'],
])->with([
    ['dashboard', 'admin.dashboard'],
    ['inspectores', 'admin.inspectores'],
    ['alertas', 'admin.alertas'],
]);

test('los roles autorizados pueden exportar el reporte de gobierno', function (string $rol, string $prefijo): void {
    $usuario = $rol === 'admin_gobierno'
        ? User::factory()->gobierno()->create(['name' => 'Administrador de prueba'])
        : User::factory()->inspector()->create(['name' => 'Inspector de prueba']);

    $this->actingAs($usuario)->get('/'.$prefijo.'/exportar')
        ->assertOk()->assertHeader('Content-type', 'text/csv; charset=utf-8');
})->with([
    ['admin_gobierno', 'admin'],
    ['admin_gobierno', 'icaa'],
    ['inspector', 'icaa'],
]);
