<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertOk()->assertViewIs('welcome')->assertSee('href="/login"', false);
});
