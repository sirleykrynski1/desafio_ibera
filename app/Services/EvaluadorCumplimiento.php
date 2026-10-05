<?php

namespace App\Services;

use App\Models\Establecimiento;
use App\Models\LimiteEfluente;

/**
 * EvaluadorCumplimiento
 *
 * Responsabilidad: Comparar análisis vs norma ICAA (Resolución 312/21)
 *
 * Entrada: Establecimiento + array de parámetros
 * Salida: Array con resultado (cumple/alerta/incumple) + detalles
 */
class EvaluadorCumplimiento
{
    /**
     * Evaluar si un análisis cumple la norma
     *
     * @param  array<string, int|float>  $parametros  Array con valores medidos
     * @return array<string, mixed> Resultado de evaluación
     */
    public function evaluar(Establecimiento $establecimiento, array $parametros): array
    {
        // 1. OBTENER LÍMITES según el tipo de destino de vuelco
        $limites = $this->obtenerLimitesDestino($establecimiento->tipo_destino_vuelco ?? 'rio');

        // 2. COMPARAR cada parámetro
        $resultados = [];
        $incumplen = [];
        $alertas = [];
        $total_parametros = 0;
        $cumplimientos = 0;

        foreach ($parametros as $nombre => $valor) {
            $total_parametros++;

            if (! isset($limites[$nombre])) {
                // Parámetro no existe en norma
                continue;
            }

            $limite = $limites[$nombre];
            $evaluacion = $this->evaluarParametro(
                $nombre,
                $valor,
                $limite
            );

            $resultados[$nombre] = $evaluacion;

            if (! $evaluacion['cumple']) {
                $incumplen[] = $nombre;
            } else {
                $cumplimientos++;
                if ($evaluacion['en_alerta']) {
                    $alertas[] = $nombre;
                }
            }
        }

        // 3. DETERMINAR ESTADO GENERAL
        $estado = $this->determinarEstado($incumplen, $alertas);
        $porcentaje = $total_parametros > 0
            ? (int) round(($cumplimientos / $total_parametros) * 100)
            : 0;

        return [
            'estado' => $estado,
            'porcentaje_cumplimiento' => $porcentaje,
            'parametros' => $resultados,
            'parametros_incumplen' => $incumplen,
            'parametros_alerta' => $alertas,
            'resumen' => [
                'cumplimientos' => $cumplimientos,
                'total_parametros' => $total_parametros,
                'mensaje' => $this->generarMensaje($estado, $porcentaje),
            ],
        ];
    }

    /**
     * Obtener límites según destino de vuelco
     *
     * Destinos:
     * - 'rio' → Límites más estrictos (río es más sensible)
     * - 'laguna' → Límites estrictos (protege Iberá)
     * - 'pozo' → Límites más relajados (infiltración)
     * - 'red_cloacal' → A cargo de AOSC (no ICAA)
     *
     * @return array<string, array<string, mixed>>
     */
    private function obtenerLimitesDestino(?string $destino): array
    {
        // Por ahora, retorna límites de ICAA (Resolución 312/21)
        // En BD, podrían almacenarse en tabla LimiteEfluente

        return [
            'temperatura' => [
                'min' => null,
                'max' => 45,
                'unidad' => '°C',
                'alerta_umbral' => 40, // A partir de esto, es alerta
            ],
            'pH' => [
                'min' => 5.5,
                'max' => 10,
                'unidad' => 'unidades pH',
                'alerta_umbral_min' => 6.5,
                'alerta_umbral_max' => 8.5,
            ],
            'sólidos_suspendidos' => [
                'min' => null,
                'max' => 35,
                'unidad' => 'mg/l',
                'alerta_umbral' => 28,
            ],
            'DBO5' => [
                'min' => null,
                'max' => 50,
                'unidad' => 'mg/l',
                'alerta_umbral' => 40,
            ],
            'DQO' => [
                'min' => null,
                'max' => 250,
                'unidad' => 'mg/l',
                'alerta_umbral' => 200,
            ],
            'detergentes' => [
                'min' => null,
                'max' => 2,
                'unidad' => 'mg/l',
                'alerta_umbral' => 1.5,
            ],
            'hidrocarburos' => [
                'min' => null,
                'max' => 30,
                'unidad' => 'mg/l',
                'alerta_umbral' => 20,
            ],
        ];
    }

    /**
     * Evaluar UN parámetro individual
     *
     * @param  array<string, mixed>  $limite
     * @return array<string, mixed>
     */
    private function evaluarParametro(string $nombre, int|float $valor, array $limite): array
    {
        $cumple = true;
        $en_alerta = false;
        $diferencia = null;

        // REVISAR máximo
        if ($limite['max'] !== null) {
            if ($valor > $limite['max']) {
                $cumple = false;
                $diferencia = $valor - $limite['max'];
            } elseif (isset($limite['alerta_umbral']) && $valor > $limite['alerta_umbral']) {
                $en_alerta = true;
                $diferencia = $limite['max'] - $valor;
            } else {
                $diferencia = $limite['max'] - $valor;
            }
        }

        // REVISAR mínimo
        if ($limite['min'] !== null) {
            if ($valor < $limite['min']) {
                $cumple = false;
                $diferencia = $limite['min'] - $valor;
            } elseif (isset($limite['alerta_umbral_min']) && $valor < $limite['alerta_umbral_min']) {
                $en_alerta = true;
                $diferencia = $valor - $limite['min'];
            } else {
                $diferencia = $valor - $limite['min'];
            }
        }

        return [
            'valor' => $valor,
            'limite_min' => $limite['min'],
            'limite_max' => $limite['max'],
            'unidad' => $limite['unidad'],
            'cumple' => $cumple,
            'en_alerta' => $en_alerta,
            'diferencia' => $diferencia,
        ];
    }

    /**
     * Determinar estado general
     *
     * @param  array<string>  $incumplen
     * @param  array<string>  $alertas
     */
    private function determinarEstado(array $incumplen, array $alertas): string
    {
        if (count($incumplen) > 0) {
            return 'incumple'; // 🔴
        }

        if (count($alertas) > 0) {
            return 'alerta'; // 🟡
        }

        return 'cumple'; // 🟢
    }

    /**
     * Generar mensaje amigable
     */
    private function generarMensaje(string $estado, int $porcentaje): string
    {
        return match ($estado) {
            'cumple' => "✅ Excelente. Tu establecimiento cumple con todas las normas ICAA ({$porcentaje}%).",
            'alerta' => '⚠️ Atención. Algunos parámetros están cercanos a los límites. Revisa pronto.',
            'incumple' => '❌ Incumplimiento. Algunos parámetros exceden los límites. Toma acción inmediata.',
        };
    }
}
