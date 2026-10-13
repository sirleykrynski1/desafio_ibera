<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('actualiza bases anteriores sin alterar usuarios y permite registrar con nombre y apellido', function (bool $obligatorio) {
    $anterior = User::factory()->create(['name' => 'Usuario anterior']);
    $datosAnteriores = $anterior->fresh()->getAttributes();
    Schema::table('users', function (Blueprint $table) use ($obligatorio): void {
        $table->string('name')->nullable(! $obligatorio)->change();
    });

    $migracion = require database_path('migrations/2026_10_13_224406_hacer_opcional_name_en_users.php');
    $migracion->up();

    $this->post('/registro', [
        'nombre' => 'Ana', 'apellido' => 'Prueba', 'email' => 'ana@example.test',
        'password' => 'ClaveLargaParaPrueba123', 'password_confirmation' => 'ClaveLargaParaPrueba123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('establecimientos.indice'));

    $this->assertAuthenticated();
    expect($anterior->fresh()->getAttributes())->toBe($datosAnteriores);
    $this->assertDatabaseHas('users', [
        'email' => 'ana@example.test', 'nombre' => 'Ana', 'apellido' => 'Prueba', 'rol' => 'propietario',
    ]);
})->with(['name obligatorio' => true, 'name ya opcional' => false]);

test('la migración admite una base que solo usa nombre y apellido', function () {
    Schema::table('users', function (Blueprint $table): void {
        $table->dropColumn('name');
    });

    $migracion = require database_path('migrations/2026_10_13_224406_hacer_opcional_name_en_users.php');
    $migracion->up();

    $usuario = User::factory()->create();
    $this->assertModelExists($usuario);
});
