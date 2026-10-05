<?php

namespace Database\Seeders;

use App\Models\AlertaClimatica;
use App\Models\Establecimiento;
use Illuminate\Database\Seeder;

class AlertaClimaticaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $establecimiento = Establecimiento::first();

        if (! $establecimiento) {
            return;
        }

        $alertas = [
            [
                'establecimiento_id' => $establecimiento->id,
                'fecha_evento' => '2026-09-20',
                'tipo' => 'Precipitaciones intensas',
                'milimetros_lluvia' => 85.50,
            ],
            [
                'establecimiento_id' => $establecimiento->id,
                'fecha_evento' => '2026-10-02',
                'tipo' => 'Alerta por tormentas y crecida de esteros',
                'milimetros_lluvia' => 112.00,
            ],
        ];

        foreach ($alertas as $alerta) {
            AlertaClimatica::create($alerta);
        }
    }
}
