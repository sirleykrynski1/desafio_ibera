<?php

namespace Database\Seeders;

use App\Models\LimiteEfluente;
use Illuminate\Database\Seeder;

class LimiteEfluenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $limites = [
            [
                'item' => 1,
                'parametro' => 'Temperatura',
                'unidad' => '°C',
                'cursos_agua' => 'Hasta 35',
                'laguna' => 'Hasta 35',
                'conducto_pluvial' => 'Hasta 35',
                'absorcion_suelo' => 'Hasta 35',
            ],
            [
                'item' => 2,
                'parametro' => 'pH',
                'unidad' => 'UpH',
                'cursos_agua' => '6,5-8,5',
                'laguna' => '6,5-8,5',
                'conducto_pluvial' => '6,5-8,5',
                'absorcion_suelo' => '5,5-10',
            ],
            [
                'item' => 3,
                'parametro' => 'Sólidos Sedimentables (2 horas)',
                'unidad' => 'ml/lts',
                'cursos_agua' => '0,5',
                'laguna' => '0,5',
                'conducto_pluvial' => '1,0',
                'absorcion_suelo' => 'NDC',
            ],
            [
                'item' => 4,
                'parametro' => 'Demanda Bioquímica de Oxígeno (DBO5)',
                'unidad' => 'mg/lts',
                'cursos_agua' => '50',
                'laguna' => '50',
                'conducto_pluvial' => '50',
                'absorcion_suelo' => '200',
            ],
            [
                'item' => 5,
                'parametro' => 'Demanda Química de Oxígeno (DQO)',
                'unidad' => 'mg/lts',
                'cursos_agua' => '250',
                'laguna' => '250',
                'conducto_pluvial' => '250',
                'absorcion_suelo' => '500',
            ],
            [
                'item' => 6,
                'parametro' => 'Sustancias Fenólicas',
                'unidad' => 'mg/lts',
                'cursos_agua' => '0,5',
                'laguna' => '0,5',
                'conducto_pluvial' => '0,5',
                'absorcion_suelo' => 'NDC',
            ],
            [
                'item' => 7,
                'parametro' => 'Hidrocarburos Totales',
                'unidad' => 'mg/lts',
                'cursos_agua' => '10',
                'laguna' => '10',
                'conducto_pluvial' => '10',
                'absorcion_suelo' => '30',
            ],
            [
                'item' => 8,
                'parametro' => 'Coliformes Fecales',
                'unidad' => 'NMP/100ml',
                'cursos_agua' => '2000',
                'laguna' => '2000',
                'conducto_pluvial' => '2000',
                'absorcion_suelo' => 'NDC',
            ],
            [
                'item' => 9,
                'parametro' => 'Plomo',
                'unidad' => 'mg/lts',
                'cursos_agua' => '0,1',
                'laguna' => '0,1',
                'conducto_pluvial' => '0,1',
                'absorcion_suelo' => '0,5',
            ],
            [
                'item' => 10,
                'parametro' => 'Cloro Libre Residual',
                'unidad' => 'mg/lts',
                'cursos_agua' => '0,5',
                'laguna' => '0,5',
                'conducto_pluvial' => '0,5',
                'absorcion_suelo' => 'NDC',
            ],
        ];

        foreach ($limites as $limite) {
            LimiteEfluente::updateOrCreate(
                ['item' => $limite['item']],
                $limite
            );
        }
    }
}
