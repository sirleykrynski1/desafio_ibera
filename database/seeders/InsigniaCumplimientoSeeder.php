<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\InsigniaCumplimiento;
use Illuminate\Database\Seeder;

class InsigniaCumplimientoSeeder extends Seeder
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

        $insignias = [
            [
                'establecimiento_id' => $establecimiento->id,
                'nombre' => 'Cero Infracciones',
                'fecha_otorgamiento' => '2026-06-30',
            ],
            [
                'establecimiento_id' => $establecimiento->id,
                'nombre' => 'Efluente Ejemplar Reserva Iberá',
                'fecha_otorgamiento' => '2026-08-15',
            ],
        ];

        foreach ($insignias as $insignia) {
            InsigniaCumplimiento::create($insignia);
        }
    }
}
