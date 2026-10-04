<?php

namespace Database\Seeders;

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\LimiteEfluente;
use Illuminate\Database\Seeder;

class AnalisisLaboratorioSeeder extends Seeder
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

        $analisis1 = AnalisisLaboratorio::create([
            'establecimiento_id' => $establecimiento->id,
            'fecha_muestra' => '2026-09-10',
            'laboratorio' => 'Laboratorio Ambiental Provincial Corrientes',
            'resultado_final' => 'Aprobado',
        ]);

        $analisis2 = AnalisisLaboratorio::create([
            'establecimiento_id' => $establecimiento->id,
            'fecha_muestra' => '2026-09-28',
            'laboratorio' => 'BioAnalítica Iberá S.A.',
            'resultado_final' => 'Pendiente',
        ]);

        // Asociar con los límites normativos en la tabla pivot
        $limitesIds = LimiteEfluente::pluck('limite_efluente_id');
        if ($limitesIds->isNotEmpty()) {
            $analisis1->limitesEfluentes()->sync($limitesIds);
            $analisis2->limitesEfluentes()->sync($limitesIds);
        }
    }
}
