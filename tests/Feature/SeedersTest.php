<?php

use App\Models\AlertaClimatica;
use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\InsigniaCumplimiento;
use App\Models\LimiteEfluente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('el seeder principal crea los usuarios demo de cada rol', function () {
    $this->seed();

    $gobierno = User::where('email', 'gobierno@ibera.gob.ar')->firstOrFail();
    $inspector = User::where('email', 'inspector@ibera.gob.ar')->firstOrFail();
    $propietario = User::where('email', 'propietario@ibera.gob.ar')->firstOrFail();

    expect($gobierno->rol)->toBe('admin_gobierno')
        ->and($inspector->rol)->toBe('inspector')
        ->and($propietario->rol)->toBe('propietario');
});

test('todos los usuarios demo pueden autenticarse con la contraseña password', function () {
    $this->seed();

    foreach (['gobierno@ibera.gob.ar', 'inspector@ibera.gob.ar', 'propietario@ibera.gob.ar'] as $email) {
        $credentials = ['email' => $email, 'password' => 'password'];

        expect(Auth::attempt($credentials))->toBeTrue();

        Auth::logout();
    }
});

test('el seeder principal carga los datos operativos de la demo', function () {
    $this->seed();

    $establecimiento = Establecimiento::firstOrFail();

    expect($establecimiento->user->rol)->toBe('propietario')
        ->and(LimiteEfluente::count())->toBe(10)
        ->and(AnalisisLaboratorio::count())->toBe(3)
        ->and(AlertaClimatica::count())->toBe(2)
        ->and(InsigniaCumplimiento::count())->toBe(2);
});

test('el seeder principal es idempotente', function () {
    $this->seed();
    $this->seed();

    expect(User::count())->toBe(3)
        ->and(Establecimiento::count())->toBe(1)
        ->and(LimiteEfluente::count())->toBe(10);
});
