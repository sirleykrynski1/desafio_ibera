<?php

namespace App\Services;

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\LimiteEfluente;
use Illuminate\Support\Facades\DB;

/**
 * RegistrarAnalisisService
 *
 * Responsabilidad: Orquestar el alta de un análisis de laboratorio de punta a punta,
 * para que la web y la API compartan un único flujo.
 *
 * Entrada: Establecimiento + ruta del PDF ya almacenado
 * Salida: array con el análisis persistido y las advertencias del extractor y del evaluador
 */
class RegistrarAnalisisService
{
    /**
     * Nombre de laboratorio usado cuando el PDF no lo permite identificar
     * (la columna `laboratorio` es NOT NULL).
     */
    private const LABORATORIO_DESCONOCIDO = 'No determinado';

    public function __construct(
        private readonly OCRExtractor $extractor,
        private readonly EvaluadorCumplimiento $evaluador,
    ) {}

    /**
     * Extrae el informe, lo evalúa contra la norma y persiste el análisis.
     *
     * @return array{analisis: AnalisisLaboratorio, advertencias: list<string>}
     */
    public function registrar(Establecimiento $establecimiento, string $rutaPdf): array
    {
        $extraccion = $this->extractor->extraerDesdeRuta($rutaPdf);
        $advertencias = $extraccion['advertencias'];
        $sinLectura = $extraccion['parametros'] === [];
        $evaluacion = null;

        if (! $sinLectura) {
            $evaluacion = $this->evaluador->evaluar($establecimiento, $this->valoresMedidos($extraccion['parametros']));
            $advertencias = [...$advertencias, ...$evaluacion['advertencias']];
        }

        if ($extraccion['laboratorio'] === null) {
            $advertencias[] = 'laboratorio_no_identificado: se registra el análisis sin nombre de laboratorio.';
        }

        $analisis = DB::transaction(function () use ($establecimiento, $rutaPdf, $extraccion, $evaluacion, $sinLectura) {
            $analisis = AnalisisLaboratorio::create([
                'establecimiento_id' => $establecimiento->getKey(),
                'fecha_muestra' => $extraccion['fecha_muestra'] ?? now()->toDateString(),
                'laboratorio' => $extraccion['laboratorio'] ?? self::LABORATORIO_DESCONOCIDO,
                'resultado_final' => 'Pendiente',
                'resultado_sugerido' => $evaluacion['estado'] ?? null,
                'estado' => $sinLectura ? 'observado' : 'evaluado',
                'ruta_pdf' => $rutaPdf,
            ]);

            $analisis->parametros()->createMany(
                array_map(
                    fn (array $parametro): array => [
                        'nombre' => $parametro['nombre'],
                        'valor_medido' => $parametro['valor_medido'],
                        'unidad' => $parametro['unidad'],
                        'detectado_por' => $parametro['detectado_por'],
                    ],
                    $extraccion['parametros']
                )
            );

            if ($evaluacion !== null) {
                $analisis->limitesEfluentes()->sync(
                    LimiteEfluente::whereIn('parametro', array_keys($evaluacion['parametros']))
                        ->pluck('limite_efluente_id')
                );
            }

            return $analisis;
        });

        return [
            'analisis' => $analisis->load(['parametros', 'limitesEfluentes']),
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Convierte la salida del extractor al arreglo que espera el evaluador:
     * clave = nombre canónico de `limite_efluente.parametro`, valor numérico.
     *
     * @param  list<array{nombre: string, valor_medido: string, unidad: string, detectado_por: string}>  $parametros
     * @return array<string, float>
     */
    private function valoresMedidos(array $parametros): array
    {
        $valores = [];

        foreach ($parametros as $parametro) {
            $valores[$parametro['nombre']] = (float) $parametro['valor_medido'];
        }

        return $valores;
    }
}
