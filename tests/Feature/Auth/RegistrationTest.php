<?php

use App\Models\User;

test('registration form is displayed on the login screen', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)->assertSee(route('registro'), false);
});

test('new users can register', function () {
    $response = $this->post('/registro', [
        'nombre' => 'Test',
        'apellido' => 'User',
        'email' => 'test@example.com',
        'password' => 'ClaveLargaParaPrueba123',
        'password_confirmation' => 'ClaveLargaParaPrueba123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('establecimientos.indice'));

    $user = User::where('email', 'test@example.com')->firstOrFail();
    expect($user->rol)->toBe('propietario');
});
