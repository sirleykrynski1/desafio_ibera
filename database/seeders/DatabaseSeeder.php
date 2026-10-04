<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'nombre' => 'Usuario',
                'apellido' => 'Prueba',
                'password' => bcrypt('password'),
                'rol' => 'propietario',
            ]
        );

        $this->call([
            LimiteEfluenteSeeder::class,
            PermisoVuelcoSeeder::class,
            AlertaClimaticaSeeder::class,
            InsigniaCumplimientoSeeder::class,
            AnalisisLaboratorioSeeder::class,
            ParametroAnalisisSeeder::class,
        ]);
    }
}
