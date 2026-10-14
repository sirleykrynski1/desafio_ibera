<?php

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->withoutVite();
    $this->travelTo(now()->setDate(2026, 10, 13)->startOfDay());
});

test('los paneles requieren iniciar sesión', function (string $ruta) {
    $this->get($ruta)->assertRedirect(route('login'));
})->with(['/dashboard', '/insignias']);

test('un propietario sin datos no recibe premios ni cumplimiento de ejemplo', function () {
    $this->actingAs(User::factory()->propietario()->create());
    $this->get('/dashboard')->assertOk()->assertSee('Todavía no tenés establecimientos registrados')
        ->assertDontSee('Hotel Paraíso')->assertDontSee('VERDE');
    $this->get('/insignias')->assertOk()->assertSee('Todavía no tenés insignias otorgadas')
        ->assertDontSee('3/6 meses')->assertDontSee('Insignia obtenida');
});

test('el resumen muestra datos propios y excluye registros ajenos vencidos o futuros', function () {
    $hotel = Establecimiento::factory()->create(['nombre' => 'Posada del Agua']);
    $ajeno = Establecimiento::factory()->create(['nombre' => 'Hotel ajeno privado']);
    foreach ([$hotel, $ajeno] as $establecimiento) {
        $establecimiento->analisisLaboratorios()->create([
            'laboratorio' => 'Laboratorio', 'fecha_muestra' => '2026-10-12',
            'estado' => 'revisado', 'resultado_sugerido' => 'incumple', 'resultado_final' => 'Rechazado',
        ]);
        $establecimiento->permisoVuelco()->create([
            'numero_expediente' => 'EXP-'.$establecimiento->id, 'fecha_emision' => '2026-01-01',
            'fecha_vencimiento' => '2026-11-20', 'estado' => 'Activo',
        ]);
        $establecimiento->insigniasCumplimiento()->create(['nombre' => 'Insignia-'.$establecimiento->id, 'fecha_otorgamiento' => '2026-09-20']);
        $establecimiento->alertasClimaticas()->create([
            'tipo' => 'Alerta-'.$establecimiento->id, 'fecha_evento' => '2026-10-12', 'periodo_hasta' => '2026-10-14',
            'milimetros_lluvia' => 125, 'consultado_en' => '2026-10-13 00:00:00',
        ]);
    }
    $hotel->insigniasCumplimiento()->create(['nombre' => 'Premio futuro', 'fecha_otorgamiento' => '2026-12-01']);
    $hotel->alertasClimaticas()->create(['tipo' => 'Aviso vencido', 'fecha_evento' => '2026-10-01', 'periodo_hasta' => '2026-10-12', 'milimetros_lluvia' => 50]);
    $hotel->alertasClimaticas()->create(['tipo' => 'Aviso antiguo sin período', 'fecha_evento' => '2026-10-01', 'milimetros_lluvia' => 50]);

    $this->actingAs($hotel->user)->get('/dashboard')->assertOk()->assertSee('Posada del Agua')
        ->assertSee('Incumple')->assertSee('Rechazado')->assertSee('20/11/2026')
        ->assertSee('Insignia-'.$hotel->id)->assertSee('Alerta-'.$hotel->id)
        ->assertDontSee('Hotel ajeno privado')->assertDontSee('Insignia-'.$ajeno->id)
        ->assertDontSee('Alerta-'.$ajeno->id)->assertDontSee('Aviso vencido')
        ->assertDontSee('Aviso antiguo sin período')->assertDontSee('Premio futuro');
    $this->get('/insignias')->assertOk()->assertSee('Insignia-'.$hotel->id)
        ->assertDontSee('Insignia-'.$ajeno->id)->assertDontSee('Premio futuro');
});

test('el selector permite otro establecimiento propio pero oculta el ajeno', function () {
    $propietario = User::factory()->propietario()->create();
    Establecimiento::factory()->for($propietario)->create(['nombre' => 'Hotel A']);
    $segundo = Establecimiento::factory()->for($propietario)->create(['nombre' => 'Hotel B']);
    $ajeno = Establecimiento::factory()->create();

    $this->actingAs($propietario)->get(route('dashboard', ['establecimiento' => $segundo->id]))
        ->assertOk()->assertViewHas('establecimiento', fn ($hotel) => $hotel->id === $segundo->id);
    $this->get(route('dashboard', ['establecimiento' => $ajeno->id]))->assertNotFound();
    $this->get(route('dashboard', ['establecimiento' => 'texto']))->assertSessionHasErrors('establecimiento');
});

test('el resumen distingue datos ausentes de resultados favorables', function () {
    $hotel = Establecimiento::factory()->create();
    $this->actingAs($hotel->user)->get('/dashboard')->assertOk()
        ->assertSee('Sin análisis registrados')->assertSee('Sin permiso registrado')
        ->assertSee('Sin alertas registradas')->assertDontSee('VERDE');
});

test('muestra la evaluación del último muestreo y mantiene separado el dictamen humano', function (string $estado, ?string $resultado, string $texto) {
    $hotel = Establecimiento::factory()->create();
    $hotel->analisisLaboratorios()->create([
        'laboratorio' => 'Más reciente', 'fecha_muestra' => '2026-10-13',
        'estado' => $estado, 'resultado_sugerido' => $resultado, 'resultado_final' => 'Pendiente',
    ]);
    $hotel->analisisLaboratorios()->create([
        'laboratorio' => 'Cargado después pero muestra anterior', 'fecha_muestra' => '2026-09-01',
        'estado' => 'revisado', 'resultado_sugerido' => 'incumple', 'resultado_final' => 'Rechazado',
    ]);

    $this->actingAs($hotel->user)->get('/dashboard')->assertOk()->assertSee($texto)->assertSee('Pendiente')
        ->assertSee('13/10/2026')->assertDontSee('Rechazado');
})->with([
    ['evaluado', 'cumple', 'Cumple'], ['evaluado', 'alerta', 'Alerta'], ['evaluado', 'incumple', 'Incumple'],
    ['observado', null, 'Requiere revisión de la lectura'], ['pendiente_lectura', null, 'Lectura pendiente'],
    ['revisado', null, 'Sin evaluación automática'],
]);

test('el permiso considera las fechas sin ocultar una revocación', function (string $estado, string $emision, string $vencimiento, string $esperado) {
    $hotel = Establecimiento::factory()->create();
    $hotel->permisoVuelco()->create([
        'numero_expediente' => 'EXP-TEST', 'fecha_emision' => $emision,
        'fecha_vencimiento' => $vencimiento, 'estado' => $estado,
    ]);
    $this->actingAs($hotel->user)->get('/dashboard')->assertOk()->assertViewHas('estadoPermiso', $esperado);
})->with([
    ['Activo', '2026-01-01', '2026-10-12', 'Vencido'],
    ['Activo', '2026-01-01', '2026-10-13', 'Activo'],
    ['Activo', '2026-10-14', '2026-12-01', 'Aún no vigente'],
    ['Revocado', '2026-01-01', '2026-10-12', 'Revocado'],
]);

test('gobierno usa su panel y no recibe insignias del propietario', function (string $rol) {
    $this->actingAs(User::factory()->create(['rol' => $rol]))->get('/dashboard')->assertRedirect(route('gestion'));
    $this->get('/insignias')->assertForbidden();
})->with(['inspector', 'admin_gobierno']);
