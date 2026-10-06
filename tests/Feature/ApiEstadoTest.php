<?php

test('la API responde con su mensaje de estado', function () {
    $response = $this->getJson('/api/estado');

    $response->assertOk();

    $response->assertExactJson([
        'mensaje' => 'La API de Desafío Iberá está funcionando',
    ]);
});
