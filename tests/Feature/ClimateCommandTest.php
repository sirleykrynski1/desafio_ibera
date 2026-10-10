<?php

use Illuminate\Support\Facades\Http;

test('prints the alert returned by Python', function () {
    config(['services.climate.url' => 'http://climate.test']);
    $payload = json_decode(file_get_contents(base_path('tests/Fixtures/climate-alert.json')), true);
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($payload)]);

    $this->artisan('clima:consultar', ['latitud' => '-28.54', 'longitud' => '-57.17'])
        ->expectsOutput(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
        ->assertSuccessful();

    Http::assertSentCount(1);
});

test('reports unavailability without printing a false safe result', function () {
    config(['services.climate.url' => 'http://climate.test']);
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response([], 503)]);

    $this->artisan('clima:consultar', ['latitud' => '-28.54', 'longitud' => '-57.17'])
        ->expectsOutput('No se pudo obtener una alerta climática válida. Revisá FastAPI y CLIMATE_API_URL.')
        ->assertFailed();

    Http::assertSentCount(1);
});

test('rejects invalid command coordinates without contacting Python', function () {
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response()]);

    $this->artisan('clima:consultar', ['latitud' => 'texto', 'longitud' => '-57.17'])
        ->expectsOutput('Ingresá latitud entre -90 y 90 y longitud entre -180 y 180.')
        ->assertFailed();

    Http::assertNothingSent();
});
