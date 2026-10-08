<?php

test('la página inicial ofrece acceso a ambos portales', function () {
    $this->withoutVite();
    $response = $this->get('/');

    $response->assertOk()->assertSee('Acceso a establecimientos')->assertSee('Acceso a gestión');
});
