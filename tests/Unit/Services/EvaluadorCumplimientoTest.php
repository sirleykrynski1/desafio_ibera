<?php

use App\Models\Establecimiento;
use App\Services\EvaluadorCumplimiento;

test('evaluador de cumplimiento aprueba parametros dentro del limite', function () {
    $evaluador = new EvaluadorCumplimiento();
    $establecimiento = new Establecimiento(['tipo_destino_vuelco' => 'rio']);

    $parametros = [
        'temperatura' => 30,
        'pH' => 7.0,
        'sólidos_suspendidos' => 20,
        'DBO5' => 30,
        'DQO' => 150,
        'detergentes' => 1.0,
        'hidrocarburos' => 10,
    ];

    $resultado = $evaluador->evaluar($establecimiento, $parametros);

    expect($resultado['estado'])->toBe('cumple')
        ->and($resultado['porcentaje_cumplimiento'])->toBe(100)
        ->and($resultado['parametros_incumplen'])->toBeEmpty()
        ->and($resultado['parametros_alerta'])->toBeEmpty();
});

test('evaluador de cumplimiento marca estado de alerta si se aproxima al limite', function () {
    $evaluador = new EvaluadorCumplimiento();
    $establecimiento = new Establecimiento(['tipo_destino_vuelco' => 'rio']);

    $parametros = [
        'temperatura' => 42, // Alerta (umbral es 40, max es 45)
        'pH' => 7.0,
    ];

    $resultado = $evaluador->evaluar($establecimiento, $parametros);

    expect($resultado['estado'])->toBe('alerta')
        ->and($resultado['parametros_alerta'])->toContain('temperatura')
        ->and($resultado['parametros_incumplen'])->toBeEmpty();
});

test('evaluador de cumplimiento marca estado de incumplimiento si excede el limite', function () {
    $evaluador = new EvaluadorCumplimiento();
    $establecimiento = new Establecimiento(['tipo_destino_vuelco' => 'rio']);

    $parametros = [
        'temperatura' => 50, // Excede max 45
        'pH' => 7.0,
    ];

    $resultado = $evaluador->evaluar($establecimiento, $parametros);

    expect($resultado['estado'])->toBe('incumple')
        ->and($resultado['parametros_incumplen'])->toContain('temperatura');
});
