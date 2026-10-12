<?php

namespace App\Services;

use App\Models\Establecimiento;
use App\Models\LimiteEfluente;

/**
 * EvaluadorCumplimiento
 *
 * Responsabilidad: Comparar análisis vs norma ICAA (Resolución 312/21)
 *
 * Entrada: Establecimiento + array de parámetros (clave = `limite_efluente.parametro`)
 * Salida: Array con resultado (cumple/alerta/incumple) + detalles
 */
class EvaluadorCumplimiento
{
    /**
     * Fracción del margen normativo que activa la alerta preventiva.
     */
    private const ALERTA_PORCENTAJE = 0.2;

    /**
     * Columnas de `limite_efluente` habilitadas según destino de vuelco.
     * El valor del destino se valida contra esta lista y nunca se interpola.
     *
     * @var list<string>
     */
    private const COLUMNAS_DESTINO = ['cursos_agua', 'laguna', 'conducto_pluvial', 'absorcion_suelo'];

    /**
     * Evaluar si un análisis cumple la norma
     *
     * @param  array<string, int|float>  $parametros  Array con valores medidos
     * @return array<string, mixed> Resultado de evaluación
     */
    public function evaluar(Establecimiento $establecimiento, array $parametros): array
    {
        // 1. OBTENER LÍMITES según el tipo de destino de vuelco
        $destino = $establecimiento->tipo_destino_vuelco;
        $columna = $this->resolverColumnaDestino($destino);
        $advertencias = [];

        if ($columna === null) {
            $columna = 'cursos_agua';
            $advertencias[] = 'destino_desconocido: '.($destino ?? 'null').'. Se aplican los límites de cursos_agua.';
        }

        $limites = $this->obtenerLimitesDestino($columna);

        // 2. COMPARAR cada parámetro
        $resultados = [];
        $incumplen = [];
        $alertas = [];
        $total_parametros = 0;
        $cumplimientos = 0;

        foreach ($parametros as $nombre => $valor) {
            if (! isset($limites[$nombre])) {
                // Parámetro no existe en norma
                continue;
            }

            $limite = $limites[$nombre];

            if (! $limite['evaluable']) {
                // NDC (no debe contener) o celda sin número: queda visible pero
                // fuera del denominador del porcentaje de cumplimiento.
                $resultados[$nombre] = $this->resultadoNoEvaluable($valor, $limite);

                continue;
            }

            $total_parametros++;

            $evaluacion = $this->evaluarParametro($valor, $limite);
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
            'advertencias' => $advertencias,
            'resumen' => [
                'cumplimientos' => $cumplimientos,
                'total_parametros' => $total_parametros,
                'mensaje' => $this->generarMensaje($estado, $porcentaje),
            ],
        ];
    }

    /**
     * Validar el destino de vuelco contra la whitelist de columnas
     *
     * @return string|null Columna válida o null si el destino no se conoce
     */
    private function resolverColumnaDestino(?string $destino): ?string
    {
        if ($destino !== null && in_array($destino, self::COLUMNAS_DESTINO, true)) {
            return $destino;
        }

        return null;
    }

    /**
     * Obtener los límites parseados de la tabla según la columna de destino
     *
     * @param  string  $columna  Una de self::COLUMNAS_DESTINO, ya validada
     * @return array<string, array{evaluable: bool, ndc: bool, min: float|null, max: float|null, alerta_min: float|null, alerta_max: float|null, unidad: string}>
     */
    private function obtenerLimitesDestino(string $columna): array
    {
        $limites = [];

        foreach (LimiteEfluente::catalogoParaEvaluacion() as $limite) {
            $limites[$limite['parametro']] = $this->parsearLimite(
                $limite[$columna],
                $limite['unidad']
            );
        }

        return $limites;
    }

    /**
     * Convertir el texto normativo de una celda en límites numéricos
     *
     * Formatos: "NDC" (no debe contener) · "Hasta 35" · "6,5-8,5" · "0,5".
     *
     * @return array{evaluable: bool, ndc: bool, min: float|null, max: float|null, alerta_min: float|null, alerta_max: float|null, unidad: string}
     */
    private function parsearLimite(?string $texto, string $unidad): array
    {
        $limpio = trim($texto ?? '');

        if ($limpio === '') {
            return $this->limiteNoEvaluable(null, $unidad, false);
        }

        if (preg_match('/^NDC$/i', $limpio) === 1) {
            // "No debe contener": lo esperado es un valor nulo o ~0.
            return $this->limiteNoEvaluable(0.0, $unidad, true);
        }

        if (preg_match('/^hasta\s+([\d.,]+)$/ui', $limpio, $coincidencias) === 1) {
            return $this->limiteEvaluable(null, $this->numero($coincidencias[1]), $unidad);
        }

        if (preg_match('/^([\d.,]+)\s*-\s*([\d.,]+)$/', $limpio, $coincidencias) === 1) {
            return $this->limiteEvaluable(
                $this->numero($coincidencias[1]),
                $this->numero($coincidencias[2]),
                $unidad
            );
        }

        if (preg_match('/^[\d.,]+$/', $limpio) === 1) {
            return $this->limiteEvaluable(null, $this->numero($limpio), $unidad);
        }

        return $this->limiteNoEvaluable(null, $unidad, false);
    }

    /**
     * Convertir un número escrito con coma decimal a float
     */
    private function numero(string $texto): float
    {
        return (float) str_replace(',', '.', $texto);
    }

    /**
     * @return array{evaluable: bool, ndc: bool, min: float|null, max: float|null, alerta_min: float|null, alerta_max: float|null, unidad: string}
     */
    private function limiteEvaluable(?float $min, ?float $max, string $unidad): array
    {
        [$alertaMin, $alertaMax] = $this->umbralesAlerta($min, $max);

        return [
            'evaluable' => true,
            'ndc' => false,
            'min' => $min,
            'max' => $max,
            'alerta_min' => $alertaMin,
            'alerta_max' => $alertaMax,
            'unidad' => $unidad,
        ];
    }

    /**
     * Límite sin número comparable: NDC (lo esperado es 0) o celda vacía/no parseable
     *
     * @return array{evaluable: bool, ndc: bool, min: float|null, max: float|null, alerta_min: float|null, alerta_max: float|null, unidad: string}
     */
    private function limiteNoEvaluable(?float $max, string $unidad, bool $ndc): array
    {
        return [
            'evaluable' => false,
            'ndc' => $ndc,
            'min' => null,
            'max' => $max,
            'alerta_min' => null,
            'alerta_max' => null,
            'unidad' => $unidad,
        ];
    }

    /**
     * Calcular la banda de alerta: 20% del máximo en límites de un solo lado
     * y 20% del rango en cada extremo cuando hay min y max
     *
     * @return array{0: float|null, 1: float|null} [alerta_min, alerta_max]
     */
    private function umbralesAlerta(?float $min, ?float $max): array
    {
        if ($min !== null && $max !== null) {
            $span = ($max - $min) * self::ALERTA_PORCENTAJE;

            return [
                round($min + $span, 6),
                round($max - $span, 6),
            ];
        }

        if ($max !== null) {
            return [null, round($max * (1 - self::ALERTA_PORCENTAJE), 6)];
        }

        if ($min !== null) {
            return [round($min * (1 + self::ALERTA_PORCENTAJE), 6), null];
        }

        return [null, null];
    }

    /**
     * Armar la entrada de salida de un parámetro no evaluable (NDC o sin número)
     *
     * @param  array{evaluable: bool, ndc: bool, min: float|null, max: float|null, alerta_min: float|null, alerta_max: float|null, unidad: string}  $limite
     * @return array<string, mixed>
     */
    private function resultadoNoEvaluable(int|float $valor, array $limite): array
    {
        return [
            'valor' => $valor,
            'limite_min' => $limite['min'],
            'limite_max' => $limite['max'],
            'unidad' => $limite['unidad'],
            'evaluable' => false,
            'ndc' => $limite['ndc'],
            'cumple' => null,
            'en_alerta' => false,
            'diferencia' => null,
        ];
    }

    /**
     * Evaluar UN parámetro individual
     *
     * @param  array{evaluable: bool, ndc: bool, min: float|null, max: float|null, alerta_min: float|null, alerta_max: float|null, unidad: string}  $limite
     * @return array<string, mixed>
     */
    private function evaluarParametro(int|float $valor, array $limite): array
    {
        $cumple = true;
        $en_alerta = false;
        $diferencia = null;

        // REVISAR máximo
        if ($limite['max'] !== null) {
            if ($valor > $limite['max']) {
                $cumple = false;
                $diferencia = $valor - $limite['max'];
            } else {
                $diferencia = $limite['max'] - $valor;
                if ($limite['alerta_max'] !== null && $valor > $limite['alerta_max']) {
                    $en_alerta = true;
                }
            }
        }

        // REVISAR mínimo
        if ($limite['min'] !== null) {
            if ($valor < $limite['min']) {
                $cumple = false;
                $diferencia = $limite['min'] - $valor;
            } else {
                $diferencia = $valor - $limite['min'];
                if ($limite['alerta_min'] !== null && $valor < $limite['alerta_min']) {
                    $en_alerta = true;
                }
            }
        }

        return [
            'valor' => $valor,
            'limite_min' => $limite['min'],
            'limite_max' => $limite['max'],
            'unidad' => $limite['unidad'],
            'evaluable' => true,
            'ndc' => false,
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
