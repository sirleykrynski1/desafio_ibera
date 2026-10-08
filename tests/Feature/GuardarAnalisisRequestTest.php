<?php

use App\Http\Requests\GuardarAnalisisRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

test('acepta un PDF sin exigir datos que se extraen del informe', function () {
    $request = new GuardarAnalisisRequest;
    $validator = Validator::make([
        'establecimiento_id' => 1,
        'pdf' => UploadedFile::fake()->create('informe.pdf', 100, 'application/pdf'),
    ], $request->rules(), $request->messages());
    expect($validator->passes())->toBeTrue();
});

test('rechaza archivos y establecimientos inválidos con mensaje claro', function (string $caso, string $campo, string $mensaje) {
    $request = new GuardarAnalisisRequest;
    $datos = ['establecimiento_id' => 1, 'pdf' => UploadedFile::fake()->create('informe.pdf', 100, 'application/pdf')];
    match ($caso) {
        'sin establecimiento' => $datos['establecimiento_id'] = '',
        'id incorrecto' => $datos['establecimiento_id'] = 'abc',
        'sin pdf' => $datos['pdf'] = null,
        'texto' => $datos['pdf'] = 'informe.pdf',
        'otro formato' => $datos['pdf'] = UploadedFile::fake()->create('informe.pdf', 100, 'text/plain'),
        'extension' => $datos['pdf'] = UploadedFile::fake()->create('informe.txt', 100, 'application/pdf'),
        'grande' => $datos['pdf'] = UploadedFile::fake()->create('informe.pdf', 10241, 'application/pdf'),
    };
    $validator = Validator::make($datos, $request->rules(), $request->messages());
    expect($validator->errors()->get($campo))->toContain($mensaje);
})->with([
    ['sin establecimiento', 'establecimiento_id', 'Seleccioná un establecimiento.'],
    ['id incorrecto', 'establecimiento_id', 'El establecimiento no es válido.'],
    ['sin pdf', 'pdf', 'Adjuntá el informe PDF.'],
    ['texto', 'pdf', 'El informe debe ser un archivo.'],
    ['otro formato', 'pdf', 'El informe debe ser un PDF.'],
    ['extension', 'pdf', 'El archivo debe tener extensión .pdf.'],
    ['grande', 'pdf', 'El PDF no puede superar los 10 MB.'],
]);

test('bloquea la carga con 403 para visitantes sin sesión', function () {
    Route::post('/api/prueba-permisos-analisis', function (GuardarAnalisisRequest $request): JsonResponse {
        return response()->json(['mensaje' => 'Carga habilitada']);
    });
    $this->postJson('/api/prueba-permisos-analisis')->assertForbidden();
});
