<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\PermisoVuelco;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermisoVuelcoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $establecimiento = Establecimiento::first();

        if (! $establecimiento) {
            $user = User::first() ?? User::create([
                'nombre' => 'Carlos',
                'apellido' => 'Gómez',
                'email' => 'propietario@ibera.gob.ar',
                'password' => bcrypt('password'),
                'rol' => 'propietario',
            ]);

            $establecimiento = Establecimiento::create([
                'user_id' => $user->id,
                'nombre' => 'Ecolodge Esteros del Iberá',
                'rubro' => 'hotel',
                'latitud' => -28.536389,
                'longitud' => -57.185278,
                'capacidad_maxima' => 80,
                'capacidad_biodigestor' => 12000,
            ]);
        }

        PermisoVuelco::updateOrCreate(
            ['establecimiento_id' => $establecimiento->id],
            [
                'numero_expediente' => 'EXP-2026-00124-AMB',
                'fecha_emision' => '2026-01-15',
                'fecha_vencimiento' => '2027-01-15',
                'estado' => 'Activo',
            ]
        );
    }
}
