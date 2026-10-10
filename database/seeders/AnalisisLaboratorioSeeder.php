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
            'resultado_sugerido' => 'cumple',
            'estado' => 'evaluado',
            'resultado_final' => 'Aprobado',
            'ruta_pdf' => 'analisis/informe-2026-09-10.pdf',
        ]);

        $analisis2 = AnalisisLaboratorio::create([
            'establecimiento_id' => $establecimiento->id,
            'fecha_muestra' => '2026-09-28',
            'laboratorio' => 'BioAnal��tica Iberǭ S.A.',
            'resultado_sugerido' => 'alerta',
            'estado' => 'evaluado',
            'resultado_final' => 'Pendiente',
            'ruta_pdf' => 'analisis/informe-2026-09-28.pdf',
        ]);

        // PDF escaneado sin capa de texto: la máquina no dictaminó, espera revisión humana.
        $analisis3 = AnalisisLaboratorio::create([
            'establecimiento_id' => $establecimiento->id,
            'fecha_muestra' => '2026-10-02',
            'laboratorio' => 'Laboratorio Ambiental Provincial Corrientes',
            'estado' => 'observado',
            'resultado_final' => 'Pendiente',
            'ruta_pdf' => 'analisis/informe-2026-10-02.pdf',
        ]);

        // Asociar con los l��mites normativos en la tabla pivot
        $limitesIds = LimiteEfluente::pluck('limite_efluente_id');
        if ($limitesIds->isNotEmpty()) {
            $analisis1->limitesEfluentes()->sync($limitesIds);
            $analisis2->limitesEfluentes()->sync($limitesIds);
            $analisis3->limitesEfluentes()->sync($limitesIds);
        }
    }
}
