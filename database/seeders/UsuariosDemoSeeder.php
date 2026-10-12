<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosDemoSeeder extends Seeder
{
    /**
     * Crea los usuarios de demostración de cada rol para la presentación.
     *
     * Todas las cuentas usan la contraseña "password".
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'gobierno@ibera.gob.ar'],
            [
                'nombre' => 'María',
                'apellido' => 'Suárez',
                'password' => 'password',
                'rol' => 'admin_gobierno',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'inspector@ibera.gob.ar'],
            [
                'nombre' => 'Jorge',
                'apellido' => 'Benítez',
                'password' => 'password',
                'rol' => 'inspector',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'propietario@ibera.gob.ar'],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Gómez',
                'password' => 'password',
                'rol' => 'propietario',
                'email_verified_at' => now(),
            ]
        );
    }
}
