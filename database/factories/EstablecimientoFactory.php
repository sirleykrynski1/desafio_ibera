<?php

namespace Database\Factories;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Establecimiento> */
class EstablecimientoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 'nombre' => fake()->company(), 'rubro' => 'hotel',
            'latitud' => -28.54, 'longitud' => -57.17,
            'capacidad_maxima' => 40, 'capacidad_biodigestor' => 5000,
            'tipo_destino_vuelco' => 'cursos_agua',
        ];
    }
}
