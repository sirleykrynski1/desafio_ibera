<?php

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->withoutVite();
});

function datosAltaGobierno(int $propietario): array
{
    return [
        'nombre' => 'Posada del Agua', 'user_id' => $propietario,
        'cuit' => '30123456789', 'ubicacion' => 'Carlos Pellegrini', 'rubro' => 'hotel',
        'tipo_destino_vuelco' => 'cursos_agua', 'latitud' => -28.536389, 'longitud' => -57.185278,
        'capacidad_maxima' => 20, 'capacidad_biodigestor' => 3000,
    ];
}

test('gobierno crea un establecimiento con propietario y coordenadas reales', function () {
    $propietario = User::factory()->propietario()->create();
    $datos = datosAltaGobierno($propietario->id);
    $this->actingAs(User::factory()->gobierno()->create())->post(route('icaa.establecimientos.guardar'), $datos)
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseHas('establecimientos', $datos);
    $this->actingAs($propietario)->get(route('dashboard'))->assertSee('Posada del Agua');
});

test('gobierno actualiza desde el formulario PUT sin perder el propietario', function () {
    $hotel = Establecimiento::factory()->create();
    $datos = datosAltaGobierno($hotel->user_id);
    $this->actingAs(User::factory()->gobierno()->create())->put(route('icaa.establecimientos.actualizar', $hotel->id), $datos)
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseHas('establecimientos', ['id' => $hotel->id] + $datos);
});

test('gobierno rechaza valores incompatibles con el establecimiento', function (string $campo, mixed $valor) {
    $datos = datosAltaGobierno(User::factory()->propietario()->create()->id);
    $datos[$campo] = $valor;
    $this->actingAs(User::factory()->gobierno()->create())->post(route('icaa.establecimientos.guardar'), $datos)
        ->assertSessionHasErrors($campo);
    $this->assertDatabaseCount('establecimientos', 0);
})->with([
    ['rubro', 'inexistente'], ['tipo_destino_vuelco', 'rio'], ['cuit', '123'],
    ['capacidad_maxima', -1], ['capacidad_biodigestor', 2.5],
    ['latitud', 91], ['longitud', -181], ['latitud', null], ['longitud', null], ['user_id', null],
]);

test('una cuenta de gobierno no puede ser asignada como propietaria', function () {
    $gobierno = User::factory()->gobierno()->create();
    $this->actingAs($gobierno)->post(route('icaa.establecimientos.guardar'), datosAltaGobierno($gobierno->id))
        ->assertSessionHasErrors(['user_id' => 'Seleccioná una cuenta con rol de propietario.']);
    $this->assertDatabaseCount('establecimientos', 0);
});

test('un inspector no puede crear establecimientos mediante el panel de gobierno', function () {
    $this->actingAs(User::factory()->inspector()->create())
        ->post(route('icaa.establecimientos.guardar'), [])->assertForbidden();
    $this->assertDatabaseCount('establecimientos', 0);
});

test('el alta requiere sesión', function () {
    $this->post(route('icaa.establecimientos.guardar'), [])->assertRedirect(route('login'));
});

test('la edición inválida conserva los datos y un registro inexistente devuelve 404', function () {
    $hotel = Establecimiento::factory()->create(['nombre' => 'Original']);
    $datos = datosAltaGobierno($hotel->user_id);
    $this->actingAs(User::factory()->gobierno()->create());
    $this->put(route('icaa.establecimientos.actualizar', $hotel->id), array_replace($datos, ['rubro' => 'otro']))->assertSessionHasErrors('rubro');
    $this->assertDatabaseHas('establecimientos', ['id' => $hotel->id, 'nombre' => 'Original']);
    $this->put(route('icaa.establecimientos.actualizar', 999999), $datos)->assertNotFound();
});

test('el formulario ofrece propietarios y campos de ubicación', function () {
    $propietario = User::factory()->propietario()->create();
    $this->actingAs(User::factory()->gobierno()->create())->get(route('admin.inspectores'))
        ->assertOk()->assertSee($propietario->email)
        ->assertSee('name="latitud"', false)->assertSee('name="longitud"', false)
        ->assertViewHas('propietarios', fn ($usuarios) => $usuarios->pluck('id')->all() === [$propietario->id]);
});

test('resumen y filtros usan el último muestreo y separan evaluación de dictamen', function () {
    $hotel = Establecimiento::factory()->create();
    $base = ['laboratorio' => 'Laboratorio', 'estado' => 'evaluado', 'resultado_final' => 'Pendiente'];
    $hotel->analisisLaboratorios()->create($base + ['fecha_muestra' => '2026-10-20', 'resultado_sugerido' => 'cumple']);
    $hotel->analisisLaboratorios()->create($base + ['fecha_muestra' => '2026-10-20', 'resultado_sugerido' => 'alerta']);
    $hotel->analisisLaboratorios()->create($base + ['fecha_muestra' => '2026-09-01', 'resultado_sugerido' => 'incumple']);
    $otro = Establecimiento::factory()->create();
    $otro->analisisLaboratorios()->create(array_replace($base, ['fecha_muestra' => '2026-10-20', 'resultado_sugerido' => 'incumple', 'resultado_final' => 'Aprobado']));
    Establecimiento::factory()->create();
    $this->actingAs(User::factory()->gobierno()->create())->get(route('admin.dashboard'))
        ->assertOk()->assertViewHas('en_norma', 0)->assertViewHas('en_alerta', 1)
        ->assertViewHas('incumplen', 1)->assertViewHas('sin_evaluacion', 1);
    $this->get(route('admin.inspectores', ['estado' => 'amarillo']))
        ->assertViewHas('establecimientos', fn ($hoteles) => $hoteles->pluck('id')->all() === [$hotel->id]);
    $this->get(route('admin.inspectores', ['estado' => 'rojo']))
        ->assertViewHas('establecimientos', fn ($hoteles) => $hoteles->pluck('id')->all() === [$otro->id]);
    $csv = $this->get(route('icaa.exportar'))->assertOk()->streamedContent();
    expect($csv)->toContain('Alerta,Pendiente')->toContain('Incumple,Aprobado');
});

test('una lectura observada no se presenta como cumplimiento', function () {
    $hotel = Establecimiento::factory()->create();
    $hotel->analisisLaboratorios()->create([
        'laboratorio' => 'Lectura incompleta', 'fecha_muestra' => '2026-10-20',
        'estado' => 'observado', 'resultado_sugerido' => 'cumple', 'resultado_final' => 'Pendiente',
    ]);
    $this->actingAs(User::factory()->gobierno()->create())->get(route('admin.dashboard'))
        ->assertViewHas('en_norma', 0)->assertViewHas('sin_evaluacion', 1);
});

test('gobierno muestra alertas vigentes reales y permite marcarlas revisadas', function () {
    $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
    $hotel = Establecimiento::factory()->create();
    $datos = ['tipo' => 'LLUVIA_INTENSA', 'fecha_evento' => '2026-10-19', 'periodo_hasta' => '2026-10-21', 'milimetros_lluvia' => 125, 'consultado_en' => now()];
    $activa = $hotel->alertasClimaticas()->create($datos + ['detalle' => ['alerta' => ['motivos' => ['Lluvias previstas en la zona']]]]);
    $hotel->alertasClimaticas()->create(array_replace($datos, ['tipo' => 'Vencida', 'periodo_hasta' => '2026-10-19']));
    $revisada = $hotel->alertasClimaticas()->create(array_replace($datos, ['tipo' => 'Ya revisada']));
    $revisada->revisada_en = now();
    $revisada->save();
    $gobierno = User::factory()->gobierno()->create();
    $this->actingAs($gobierno)->get(route('admin.alertas'))->assertOk()
        ->assertSee('Lluvias previstas en la zona')->assertSee('125.00 mm')
        ->assertDontSee('Vencida')->assertDontSee('Ya revisada')->assertSee(route('alertas.revisar', $activa), false);
    $this->get(route('icaa.establecimientos.ver', $hotel->id))->assertSee('Lluvias previstas en la zona');
    $this->patch(route('alertas.revisar', $activa))->assertRedirect();
    $this->assertDatabaseHas('alerta_climatica', ['alerta_climatica_id' => $activa->getKey(), 'revisada_por' => $gobierno->id]);
    $this->get(route('admin.alertas'))->assertSee('Todo en orden');
});

test('una alerta sin período usa su fecha de evento y el detalle no mezcla establecimientos', function () {
    $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
    $hotel = Establecimiento::factory()->create();
    $otro = Establecimiento::factory()->create();
    $hotel->alertasClimaticas()->create(['tipo' => 'Aviso de hoy', 'fecha_evento' => '2026-10-20', 'milimetros_lluvia' => 80]);
    $hotel->alertasClimaticas()->create(['tipo' => 'Aviso viejo', 'fecha_evento' => '2026-10-19', 'milimetros_lluvia' => 80]);
    $otro->alertasClimaticas()->create(['tipo' => 'Aviso de otro hotel', 'fecha_evento' => '2026-10-20', 'milimetros_lluvia' => 80]);
    $this->actingAs(User::factory()->gobierno()->create())->get(route('icaa.establecimientos.ver', $hotel->id))
        ->assertSee('Aviso de hoy')->assertDontSee('Aviso viejo')->assertDontSee('Aviso de otro hotel');
});

test('el reporte neutraliza fórmulas en nombres aportados por usuarios', function () {
    Establecimiento::factory()->create(['nombre' => '=1+1']);
    $csv = $this->actingAs(User::factory()->gobierno()->create())->get(route('icaa.exportar'))->streamedContent();
    expect($csv)->toContain("'=1+1");
});
