<?php

test('the home page redirects to the dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
