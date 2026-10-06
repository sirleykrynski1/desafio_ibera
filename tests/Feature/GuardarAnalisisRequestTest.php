<?php

use App\Http\Requests\GuardarAnalisisRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

test('acepta la fecha actual y un laboratorio válido', function () {
    $this->travelTo(new DateTimeImmutable('2026-10-06 12:00:00'));
    $request = new GuardarAnalisisRequest;

    $validator = Validator::make([
        'fecha_muestra' => '2026-10-06',
        'laboratorio' => 'laboratorio de prueba',
    ], $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

test('rechaza datos incorrectos con un mensaje claro', function (
    string $campo,
    mixed $valor,
    string $mensaje,
) {
    $this->travelTo(new DateTimeImmutable('2026-10-06 12:00:00'));
    $request = new GuardarAnalisisRequest;
    $datos = [
        'fecha_muestra' => '2026-10-05',
        'laboratorio' => 'Laboratorio de prueba',
    ];
    $datos[$campo] = $valor;

    $validator = Validator::make(
        $datos,
        $request->rules(),
        $request->messages(),
    );

    expect($validator->errors()->first($campo))->toBe($mensaje);
})->with([
    'fecha vacía' => [
        'fecha_muestra', '', 'La fecha de la muestra es obligatoria.',
    ],
    'formato incorrecto' => [
        'fecha_muestra', '05/10/2026', 'La fecha debe tener el formato AAAA-MM-DD.',
    ],
    'fecha futura' => [
        'fecha_muestra', '2026-10-07', 'La fecha de la muestra no puede ser futura.',
    ],
    'laboratorio vacío' => [
        'laboratorio', '', 'El nombre del laboratorio es obligatorio.',
    ],
    'laboratorio numérico' => [
        'laboratorio', 123, 'El nombre del laboratorio debe ser texto.',
    ],
    'nombre demasiado largo' => [
        'laboratorio', str_repeat('a', 256), 'El nombre del laboratorio no puede superar los 255 caracteres.',
    ],
]);

test('bloquea la carga con 403 mientras no se habiliten los permisos', function () {
    Route::post('/api/prueba-permisos-analisis', function (GuardarAnalisisRequest $request): JsonResponse {
        return response()->json(['mensaje' => 'Carga habilitada']);
    });

    $response = $this->postJson('/api/prueba-permisos-analisis', [
        'fecha_muestra' => '2026-10-05',
        'laboratorio' => 'Laboratorio de prueba',
    ]);

    $response->assertForbidden();
});
