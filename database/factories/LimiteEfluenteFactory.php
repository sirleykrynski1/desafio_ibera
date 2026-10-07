<?php

namespace Database\Factories;

use App\Models\LimiteEfluente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LimiteEfluente>
 */
class LimiteEfluenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $identificador = fake()->unique()->numberBetween(1, 99999);

        return [
            'item' => $identificador,
            'parametro' => 'Parámetro de prueba '.$identificador,
            'unidad' => 'mg/lts',
            'cursos_agua' => 'Hasta 50',
            'laguna' => 'Hasta 50',
            'conducto_pluvial' => 'Hasta 50',
            'absorcion_suelo' => 'Hasta 50',
        ];
    }
}
