<?php

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\User;
use App\Services\GeneradorFUR;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Crea un establecimiento con todos los datos que el FUR necesita leer.
 *
 * @param  array<string, mixed>  $atributos
 */
function crearEstablecimiento(array $atributos = []): Establecimiento
{
    return Establecimiento::create(array_merge([
        'user_id' => User::factory()->create()->getKey(),
        'nombre' => 'Hotel Costa del Iberá',
        'rubro' => 'hotel',
        'cuit' => '20304050607',
        'ubicacion' => 'Ruta Provincial 12 km 8',
        'latitud' => -27.3667,
        'longitud' => -55.8961,
        'tipo_destino_vuelco' => 'laguna',
        'capacidad_maxima' => 40,
        'capacidad_biodigestor' => 5000,
    ], $atributos));
}

function crearAnalisis(Establecimiento $establecimiento, string $fechaMuestra, string $resultado): AnalisisLaboratorio
{
    return AnalisisLaboratorio::create([
        'establecimiento_id' => $establecimiento->getKey(),
        'fecha_muestra' => $fechaMuestra,
        'laboratorio' => 'Laboratorio Ambiental de Prueba',
        'resultado_final' => $resultado,
    ]);
}

/**
 * Invoca un método privado del servicio para poder verificar los cálculos
 * que no quedan expuestos en el PDF.
 */
function invocarPrivado(GeneradorFUR $servicio, string $metodo, mixed ...$argumentos): mixed
{
    $reflexion = new ReflectionMethod($servicio, $metodo);

    return $reflexion->invoke($servicio, ...$argumentos);
}

test('genera un pdf descargable con el nombre del establecimiento', function () {
    $establecimiento = crearEstablecimiento();
    crearAnalisis($establecimiento, '2026-09-10', 'Aprobado');

    $respuesta = (new GeneradorFUR)->generar($establecimiento);

    expect($respuesta->getStatusCode())->toBe(200)
        ->and($respuesta->headers->get('content-disposition'))
        ->toContain('FUR_hotel-costa-del-ibera_'.now('America/Argentina/Buenos_Aires')->format('Y-m-d').'.pdf')
        ->and($respuesta->getContent())->toStartWith('%PDF-');
});

test('traduce el enum resultado final al semaforo de la resolucion', function () {
    $establecimiento = crearEstablecimiento();

    crearAnalisis($establecimiento, '2026-07-10', 'Aprobado');
    crearAnalisis($establecimiento, '2026-08-10', 'Pendiente');
    crearAnalisis($establecimiento, '2026-09-10', 'Rechazado');

    $estadisticas = invocarPrivado(
        new GeneradorFUR,
        'calcularEstadisticas',
        $establecimiento->analisisLaboratorios()->orderByDesc('fecha_muestra')->get()
    );

    expect($estadisticas['total'])->toBe(3)
        ->and($estadisticas['cumple'])->toBe(1)
        ->and($estadisticas['alerta'])->toBe(1)
        ->and($estadisticas['incumple'])->toBe(1)
        ->and($estadisticas['porcentaje_cumplimiento'])->toBe(33)
        ->and($estadisticas['meses_consecutivos_cumple'])->toBe(0)
        ->and($estadisticas['obtiene_insignia'])->toBeFalse();
});

test('cuenta meses calendario consecutivos y no solo analisis consecutivos', function () {
    $establecimiento = crearEstablecimiento();

    // Seis meses seguidos, dos análisis en el mismo mes: la racha son 6 meses, no 7 análisis.
    crearAnalisis($establecimiento, '2026-04-05', 'Aprobado');
    crearAnalisis($establecimiento, '2026-04-20', 'Aprobado');
    foreach (['2026-05', '2026-06', '2026-07', '2026-08', '2026-09'] as $mes) {
        crearAnalisis($establecimiento, $mes.'-10', 'Aprobado');
    }

    $estadisticas = invocarPrivado(
        new GeneradorFUR,
        'calcularEstadisticas',
        $establecimiento->analisisLaboratorios()->orderByDesc('fecha_muestra')->get()
    );

    expect($estadisticas['meses_consecutivos_cumple'])->toBe(6)
        ->and($estadisticas['obtiene_insignia'])->toBeTrue();
});

test('rompe la racha de meses en verde cuando aparece un incumplimiento', function () {
    $establecimiento = crearEstablecimiento();

    crearAnalisis($establecimiento, '2026-08-10', 'Aprobado');
    crearAnalisis($establecimiento, '2026-09-10', 'Rechazado');
    crearAnalisis($establecimiento, '2026-10-10', 'Aprobado');

    $estadisticas = invocarPrivado(
        new GeneradorFUR,
        'calcularEstadisticas',
        $establecimiento->analisisLaboratorios()->orderByDesc('fecha_muestra')->get()
    );

    expect($estadisticas['meses_consecutivos_cumple'])->toBe(1)
        ->and($estadisticas['obtiene_insignia'])->toBeFalse();
});

test('no divide por cero cuando el establecimiento no tiene analisis', function () {
    $establecimiento = crearEstablecimiento();

    $estadisticas = invocarPrivado(new GeneradorFUR, 'calcularEstadisticas', $establecimiento->analisisLaboratorios()->get());

    expect($estadisticas['total'])->toBe(0)
        ->and($estadisticas['porcentaje_cumplimiento'])->toBe(0)
        ->and($estadisticas['meses_consecutivos_cumple'])->toBe(0);

    $respuesta = (new GeneradorFUR)->generar($establecimiento);

    expect($respuesta->getContent())->toStartWith('%PDF-');
});
