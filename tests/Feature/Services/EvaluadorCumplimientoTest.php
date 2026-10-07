<?php

use App\Models\Establecimiento;
use App\Models\LimiteEfluente;
use App\Services\EvaluadorCumplimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Carga en la BD los límites normativos que el evaluador va a leer.
 *
 * @param  array<string, string|null>  $limites  parametro => texto de la columna de destino
 */
function crearLimitesParaEvaluar(array $limites, string $columna = 'cursos_agua'): void
{
    foreach ($limites as $parametro => $texto) {
        LimiteEfluente::factory()->create([
            'parametro' => $parametro,
            $columna => $texto,
        ]);
    }
}

/**
 * @param  array<string, int|float>  $parametros
 * @return array<string, mixed>
 */
function evaluarConDestino(array $parametros, ?string $destino = 'cursos_agua'): array
{
    return (new EvaluadorCumplimiento)->evaluar(
        new Establecimiento(['tipo_destino_vuelco' => $destino]),
        $parametros
    );
}

test('aprueba los parametros dentro del limite', function () {
    crearLimitesParaEvaluar([
        'Temperatura' => 'Hasta 35',
        'pH' => '6,5-8,5',
        'Demanda Bioquímica de Oxígeno (DBO5)' => '50',
    ]);

    $resultado = evaluarConDestino([
        'Temperatura' => 25,
        'pH' => 7.0,
        'Demanda Bioquímica de Oxígeno (DBO5)' => 30,
    ]);

    expect($resultado['estado'])->toBe('cumple')
        ->and($resultado['porcentaje_cumplimiento'])->toBe(100)
        ->and($resultado['resumen']['total_parametros'])->toBe(3)
        ->and($resultado['parametros_incumplen'])->toBeEmpty()
        ->and($resultado['parametros_alerta'])->toBeEmpty()
        ->and($resultado['advertencias'])->toBeEmpty();
});

test('marca estado de alerta si se aproxima al limite maximo', function () {
    crearLimitesParaEvaluar(['Temperatura' => 'Hasta 35']);

    $resultado = evaluarConDestino(['Temperatura' => 30]);

    expect($resultado['estado'])->toBe('alerta')
        ->and($resultado['parametros_alerta'])->toContain('Temperatura')
        ->and($resultado['parametros_incumplen'])->toBeEmpty()
        ->and($resultado['parametros']['Temperatura']['limite_max'])->toBe(35.0);
});

test('marca estado de incumplimiento si excede el limite', function () {
    crearLimitesParaEvaluar(['Temperatura' => 'Hasta 35']);

    $resultado = evaluarConDestino(['Temperatura' => 36]);

    expect($resultado['estado'])->toBe('incumple')
        ->and($resultado['parametros_incumplen'])->toContain('Temperatura');
});

test('marca alerta cuando el pH se acerca a un extremo del rango', function (float $valor) {
    crearLimitesParaEvaluar(['pH' => '6,5-8,5']);

    $resultado = evaluarConDestino(['pH' => $valor]);

    expect($resultado['estado'])->toBe('alerta')
        ->and($resultado['parametros_alerta'])->toContain('pH')
        ->and($resultado['parametros_incumplen'])->toBeEmpty();
})->with([
    'cerca del mínimo' => [6.8],
    'cerca del máximo' => [8.2],
]);

test('no marca alerta dentro de la banda permitida del rango', function (float $valor) {
    crearLimitesParaEvaluar(['pH' => '6,5-8,5']);

    $resultado = evaluarConDestino(['pH' => $valor]);

    expect($resultado['estado'])->toBe('cumple')
        ->and($resultado['parametros_alerta'])->toBeEmpty();
})->with([
    'extremo inferior de la banda' => [6.9],
    'centro del rango' => [7.0],
    'extremo superior de la banda' => [8.1],
]);

test('marca incumplimiento cuando el valor queda fuera del rango normativo', function (float $valor) {
    crearLimitesParaEvaluar(['pH' => '6,5-8,5']);

    $resultado = evaluarConDestino(['pH' => $valor]);

    expect($resultado['estado'])->toBe('incumple')
        ->and($resultado['parametros_incumplen'])->toContain('pH');
})->with([
    'por debajo del minimo' => [6.0],
    'por encima del maximo' => [9.0],
]);

test('interpreta un numero suelto con coma decimal como limite maximo', function () {
    LimiteEfluente::factory()->create([
        'parametro' => 'Sólidos Sedimentables (2 horas)',
        'unidad' => 'ml/lts',
        'cursos_agua' => '0,5',
    ]);

    $resultado = evaluarConDestino(['Sólidos Sedimentables (2 horas)' => 0.6]);

    expect($resultado['estado'])->toBe('incumple')
        ->and($resultado['parametros']['Sólidos Sedimentables (2 horas)'])
        ->toMatchArray([
            'limite_min' => null,
            'limite_max' => 0.5,
            'unidad' => 'ml/lts',
        ]);
});

test('excluye los parametros NDC del porcentaje de cumplimiento', function () {
    crearLimitesParaEvaluar([
        'Temperatura' => 'Hasta 35',
        'Plomo' => 'NDC',
    ]);

    $resultado = evaluarConDestino([
        'Temperatura' => 25,
        'Plomo' => 0,
    ]);

    expect($resultado['porcentaje_cumplimiento'])->toBe(100)
        ->and($resultado['resumen']['total_parametros'])->toBe(1)
        ->and($resultado['parametros']['Plomo'])
        ->toMatchArray([
            'evaluable' => false,
            'ndc' => true,
            'limite_min' => null,
            'limite_max' => 0.0,
            'cumple' => null,
        ])
        ->and($resultado['parametros_incumplen'])->toBeEmpty()
        ->and($resultado['parametros_alerta'])->toBeEmpty();
});

test('no cuenta los parametros que no existen en la norma', function () {
    crearLimitesParaEvaluar(['Temperatura' => 'Hasta 35']);

    $resultado = evaluarConDestino([
        'Temperatura' => 25,
        'Detergentes' => 1.0,
    ]);

    expect($resultado['resumen']['total_parametros'])->toBe(1)
        ->and($resultado['parametros'])->not->toHaveKey('Detergentes')
        ->and($resultado['porcentaje_cumplimiento'])->toBe(100);
});

test('deja sin evaluar los parametros con la celda normativa vacia', function () {
    crearLimitesParaEvaluar(['Coliformes Fecales' => null]);

    $resultado = evaluarConDestino(['Coliformes Fecales' => 200]);

    expect($resultado['resumen']['total_parametros'])->toBe(0)
        ->and($resultado['porcentaje_cumplimiento'])->toBe(0)
        ->and($resultado['parametros']['Coliformes Fecales'])
        ->toMatchArray([
            'evaluable' => false,
            'ndc' => false,
            'limite_max' => null,
        ]);
});

test('lee la columna de limites que corresponde al destino', function () {
    LimiteEfluente::factory()->create([
        'parametro' => 'Temperatura',
        'cursos_agua' => 'Hasta 35',
        'laguna' => 'Hasta 40',
    ]);

    $resultado = evaluarConDestino(['Temperatura' => 36], 'laguna');

    expect($resultado['parametros']['Temperatura']['limite_max'])->toBe(40.0)
        ->and($resultado['parametros_incumplen'])->toBeEmpty()
        ->and($resultado['advertencias'])->toBeEmpty();
});

test('aplica los limites de cursos_agua y avisa cuando el destino no se conoce', function (?string $destino) {
    crearLimitesParaEvaluar(['Temperatura' => 'Hasta 35']);

    $resultado = evaluarConDestino(['Temperatura' => 36], $destino);

    expect($resultado['advertencias'])->toHaveCount(1)
        ->and($resultado['advertencias'][0])->toContain('destino_desconocido')
        ->and($resultado['parametros']['Temperatura']['limite_max'])->toBe(35.0)
        ->and($resultado['parametros_incumplen'])->toContain('Temperatura');
})->with([
    'destino null' => [null],
    'destino legado sin columna' => ['rio'],
]);
