<?php

namespace Database\Seeders;

use App\Models\AnalisisLaboratorio;
use App\Models\ParametroAnalisis;
use Illuminate\Database\Seeder;

class ParametroAnalisisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $analisisList = AnalisisLaboratorio::all();

        foreach ($analisisList as $analisis) {
            $parametros = [
                [
                    'analisis_laboratorio_id' => $analisis->analisis_laboratorio_id,
                    'nombre' => 'pH',
                    'valor_medido' => '7.3',
                    'unidad' => 'UpH',
                ],
                [
                    'analisis_laboratorio_id' => $analisis->analisis_laboratorio_id,
                    'nombre' => 'Temperatura',
                    'valor_medido' => '23.8',
                    'unidad' => '°C',
                ],
                [
                    'analisis_laboratorio_id' => $analisis->analisis_laboratorio_id,
                    'nombre' => 'Demanda Bioquímica de Oxígeno (DBO5)',
                    'valor_medido' => '32.5',
                    'unidad' => 'mg/lts',
                ],
                [
                    'analisis_laboratorio_id' => $analisis->analisis_laboratorio_id,
                    'nombre' => 'Demanda Química de Oxígeno (DQO)',
                    'valor_medido' => '175.0',
                    'unidad' => 'mg/lts',
                ],
                [
                    'analisis_laboratorio_id' => $analisis->analisis_laboratorio_id,
                    'nombre' => 'Sólidos Sedimentables (2 horas)',
                    'valor_medido' => '0.3',
                    'unidad' => 'ml/lts',
                ],
                [
                    'analisis_laboratorio_id' => $analisis->analisis_laboratorio_id,
                    'nombre' => 'Coliformes Fecales',
                    'valor_medido' => '450',
                    'unidad' => 'NMP/100ml',
                ],
            ];

            foreach ($parametros as $parametro) {
                ParametroAnalisis::create($parametro);
            }
        }
    }
}
