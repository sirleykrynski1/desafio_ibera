<?php

use App\Services\ClimateApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.climate.url' => 'http://climate.test']);
});

test('returns the Python alert without recalculating its risk', function () {
    $payload = json_decode(file_get_contents(base_path('tests/Fixtures/climate-alert.json')), true);
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($payload)]);

    $result = app(ClimateApiClient::class)->fetchAlert(-28.54, -57.17);

    expect($result)->toBe($payload);
    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request['latitud'] === -28.54
        && $request['longitud'] === -57.17
        && $request->hasHeader('Accept', 'application/json'));
    Http::assertSentCount(1);
});

test('preserves zero precipitation and an inactive alert', function () {
    $payload = json_decode(file_get_contents(base_path('tests/Fixtures/climate-alert.json')), true);
    $payload['precipitacion_acumulada_mm'] = 0;
    $payload['alerta'] = ['activa' => false, 'codigo' => null, 'motivos' => []];
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($payload)]);

    $result = app(ClimateApiClient::class)->fetchAlert(-28.54, -57.17);

    expect($result)->toBe($payload);
    Http::assertSentCount(1);
});

test('returns unavailable for unsuccessful HTTP responses', function (int $status) {
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response(['estado' => 'no_disponible'], $status)]);

    $result = app(ClimateApiClient::class)->fetchAlert(-28.54, -57.17);

    expect($result)->toBeNull();
    Http::assertSentCount(1);
})->with([302, 422, 503]);

test('returns unavailable on a connection failure or timeout', function () {
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::failedConnection()]);

    $result = app(ClimateApiClient::class)->fetchAlert(-28.54, -57.17);

    expect($result)->toBeNull();
    Http::assertSentCount(1);
});

test('rejects malformed or incomplete successful responses', function (string $body) {
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($body)]);

    $result = app(ClimateApiClient::class)->fetchAlert(-28.54, -57.17);

    expect($result)->toBeNull();
    Http::assertSentCount(1);
})->with(['<html>Error</html>', 'null', '{}', '{"estado":"disponible"}']);

test('rejects incompatible or inconsistent forecast fields', function (string $field, mixed $value) {
    $payload = json_decode(file_get_contents(base_path('tests/Fixtures/climate-alert.json')), true);
    data_set($payload, $field, $value);
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response($payload)]);

    $result = app(ClimateApiClient::class)->fetchAlert(-28.54, -57.17);

    expect($result)->toBeNull();
    Http::assertSentCount(1);
})->with([
    ['version', '2'],
    ['estado', 'no_disponible'],
    ['ubicacion.latitud', -29],
    ['ubicacion.longitud', -58],
    ['precipitacion_acumulada_mm', -1],
    ['periodo.hasta', '2026-10-03'],
    ['alerta.activa', 'false'],
    ['alerta.activa', false],
    ['alerta.codigo', null],
    ['alerta.motivos', []],
]);

test('rejects invalid coordinates without contacting Python', function (float $latitude, float $longitude) {
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => Http::response()]);

    expect(fn () => app(ClimateApiClient::class)->fetchAlert($latitude, $longitude))
        ->toThrow(InvalidArgumentException::class, 'Las coordenadas están fuera del rango permitido.');
    Http::assertNothingSent();
})->with([[91.0, 0.0], [-91.0, 0.0], [0.0, 181.0], [0.0, -181.0], [NAN, 0.0], [0.0, INF]]);

test('uses the configured connection and response timeouts', function () {
    Http::preventStrayRequests();
    Http::fake(['http://climate.test/api/v1/alerta-climatica*' => function (Request $request, array $options) {
        expect($options['connect_timeout'])->toBe(3);
        expect($options['timeout'])->toBe(15);
        expect($options['allow_redirects'])->toBeFalse();

        return Http::response([], 503);
    }]);

    app(ClimateApiClient::class)->fetchAlert(-28.54, -57.17);

    Http::assertSentCount(1);
});
