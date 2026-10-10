<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertOk()->assertSee('Acceso a establecimientos')->assertSee('Acceso a gestión');
});
