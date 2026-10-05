<?php

namespace App\Services;

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * GeneradorFUR
 *
 * Responsabilidad: Generar el PDF del Formulario Único de Registro pre-llenado
 * con los datos que el establecimiento ya tiene cargados en la plataforma.
 *
 * Entrada: Establecimiento + (opcional) PermisoVuelco + historial de análisis
 * Salida: Response con el PDF descargable
 */
class GeneradorFUR
{
    /**
     * Traduce el enum `resultado_final` de la BD al semáforo de la resolución.
     *
     * @var array<string, array{estado: string, color: string, texto: string}>
     */
    private const SEMAFORO = [
        'Aprobado' => ['estado' => 'cumple', 'color' => '#1a7f37', 'texto' => 'CUMPLE'],
        'Pendiente' => ['estado' => 'alerta', 'color' => '#b58105', 'texto' => 'ALERTA'],
        'Rechazado' => ['estado' => 'incumple', 'color' => '#b02a37', 'texto' => 'INCUMPLE'],
    ];

    /**
     * Meses en verde necesarios para obtener la insignia de establecimiento recomendado.
     */
    private const MESES_PARA_INSIGNIA = 6;

    /**
     * Etiquetas legibles para los cuerpos receptores de la Resolución 312/21.
     *
     * @var array<string, string>
     */
    private const DESTINOS = [
        'cursos_agua' => 'Curso de agua',
        'laguna' => 'Laguna',
        'conducto_pluvial' => 'Conducto pluvial',
        'absorcion_suelo' => 'Absorción por suelo',
    ];

    /**
     * Etiquetas legibles para el enum `rubro` de Establecimiento.
     *
     * @var array<string, string>
     */
    private const RUBROS = [
        'hotel' => 'Hotel',
        'gastronomico' => 'Gastronómico',
        'comercio' => 'Comercio',
    ];

    /**
     * Genera el PDF del FUR y lo devuelve como descarga.
     *
     * @param  array{ultimos_analisis?: int, incluir_plano?: bool}  $opciones
     */
    public function generar(Establecimiento $establecimiento, array $opciones = []): Response
    {
        $opciones = array_merge([
            'ultimos_analisis' => 12,
            'incluir_plano' => false,
        ], $opciones);

        $establecimiento->loadMissing(['permisoVuelco', 'user']);

        $analisis = $this->obtenerUltimosAnalisis($establecimiento, $opciones['ultimos_analisis']);
        $estadisticas = $this->calcularEstadisticas($analisis);

        $pdf = Pdf::loadHTML($this->generarHTML($establecimiento, $analisis, $estadisticas))
            ->setPaper('a4')
            ->setOptions(['isRemoteEnabled' => false]);

        return $pdf->download(sprintf(
            'FUR_%s_%s.pdf',
            Str::slug($establecimiento->nombre),
            Carbon::now('America/Argentina/Buenos_Aires')->format('Y-m-d')
        ));
    }

    /**
     * Trae los últimos análisis con el conteo de parámetros ya resuelto.
     *
     * @return Collection<int, AnalisisLaboratorio>
     */
    private function obtenerUltimosAnalisis(Establecimiento $establecimiento, int $cantidad): Collection
    {
        return AnalisisLaboratorio::query()
            ->where('establecimiento_id', $establecimiento->getKey())
            ->withCount('parametros')
            ->orderByDesc('fecha_muestra')
            ->limit(max($cantidad, 1))
            ->get();
    }

    /**
     * Calcula el semáforo agregado y los indicadores del formulario.
     *
     * @param  Collection<int, AnalisisLaboratorio>  $analisis
     * @return array{total: int, cumple: int, alerta: int, incumple: int, porcentaje_cumplimiento: int, meses_consecutivos_cumple: int, obtiene_insignia: bool}
     */
    private function calcularEstadisticas(Collection $analisis): array
    {
        $cumple = $analisis->where('resultado_final', 'Aprobado')->count();
        $alerta = $analisis->where('resultado_final', 'Pendiente')->count();
        $incumple = $analisis->where('resultado_final', 'Rechazado')->count();
        $total = $analisis->count();

        $mesesConsecutivos = $this->calcularMesesConsecutivos($analisis);

        return [
            'total' => $total,
            'cumple' => $cumple,
            'alerta' => $alerta,
            'incumple' => $incumple,
            'porcentaje_cumplimiento' => $total > 0 ? (int) round(($cumple / $total) * 100) : 0,
            'meses_consecutivos_cumple' => $mesesConsecutivos,
            'obtiene_insignia' => $mesesConsecutivos >= self::MESES_PARA_INSIGNIA,
        ];
    }

    /**
     * Cuenta cuántos meses calendario seguidos vienen en verde.
     *
     * Recibe el historial ya ordenado de más reciente a más antiguo, así que
     * alcanza con caminar hacia atrás y cortar en el primer salto de mes.
     *
     * @param  Collection<int, AnalisisLaboratorio>  $analisis
     */
    private function calcularMesesConsecutivos(Collection $analisis): int
    {
        $mesesAprobados = [];

        foreach ($analisis as $registro) {
            if ($registro->resultado_final !== 'Aprobado') {
                break;
            }

            $mesesAprobados[$registro->fecha_muestra->format('Y-m')] = true;
        }

        if ($mesesAprobados === []) {
            return 0;
        }

        $claves = array_keys($mesesAprobados);
        $esperado = Carbon::createFromFormat('Y-m-d', $claves[0].'-01')->subMonth();
        $consecutivos = 1;

        foreach (array_slice($claves, 1) as $clave) {
            if ($clave !== $esperado->format('Y-m')) {
                break;
            }

            $consecutivos++;
            $esperado->subMonth();
        }

        return $consecutivos;
    }

    /**
     * Arma el HTML que dompdf convierte en PDF.
     *
     * No se usan emojis: dompdf no incluye una fuente de emoji y los
     * renderiza como cuadros vacíos.
     *
     * @param  Collection<int, AnalisisLaboratorio>  $analisis
     * @param  array{total: int, cumple: int, alerta: int, incumple: int, porcentaje_cumplimiento: int, meses_consecutivos_cumple: int, obtiene_insignia: bool}  $estadisticas
     */
    private function generarHTML(Establecimiento $establecimiento, Collection $analisis, array $estadisticas): string
    {
        return implode(PHP_EOL, [
            $this->estilos(),
            $this->encabezado(),
            $this->bloqueEstablecimiento($establecimiento),
            $this->bloquePermiso($establecimiento),
            $this->bloqueEstadisticas($estadisticas),
            $estadisticas['obtiene_insignia'] ? $this->bloqueInsignia($estadisticas) : '',
            $this->bloqueAnalisis($analisis, $estadisticas),
            $this->pieDePagina(),
        ]);
    }

    private function estilos(): string
    {
        return <<<'HTML'
        <style>
            @page { margin: 25px 30px; }
            body { font-family: DejaVu Sans, sans-serif; color: #1f2328; font-size: 10px; }
            .encabezado { text-align: center; border-bottom: 2px solid #003da5; padding-bottom: 10px; margin-bottom: 18px; }
            .encabezado h1 { color: #003da5; font-size: 17px; margin: 0 0 3px; }
            .encabezado .organismo { font-size: 12px; font-weight: bold; margin: 0 0 4px; }
            .encabezado p { font-size: 9px; color: #57606a; margin: 0; }
            .bloque { border: 1px solid #d0d7de; border-radius: 3px; padding: 10px 12px; margin-bottom: 12px; }
            .bloque h2 { color: #003da5; font-size: 11px; margin: 0 0 8px; padding-bottom: 4px; border-bottom: 1px solid #003da5; }
            table { width: 100%; border-collapse: collapse; }
            table.datos td { padding: 3px 0; vertical-align: top; }
            table.datos td.etiqueta { width: 38%; font-weight: bold; color: #444; }
            table.grilla { font-size: 9px; }
            table.grilla th { background-color: #003da5; color: #fff; padding: 5px 6px; text-align: left; }
            table.grilla td { padding: 5px 6px; border-bottom: 1px solid #eaeef2; }
            .indicadores { width: 100%; }
            .indicadores td { width: 33%; text-align: center; border: 1px solid #d0d7de; padding: 8px 4px; }
            .indicadores .numero { font-size: 18px; font-weight: bold; color: #003da5; }
            .indicadores .leyenda { font-size: 8px; color: #57606a; text-transform: uppercase; }
            .insignia { border: 2px solid #b58105; background-color: #fff8c5; text-align: center; padding: 10px; margin-bottom: 12px; }
            .insignia .titulo { font-size: 13px; font-weight: bold; color: #7a5a00; }
            .insignia .detalle { font-size: 9px; color: #6b5b17; }
            .pie { border-top: 1px solid #d0d7de; margin-top: 18px; padding-top: 8px; text-align: center; font-size: 8px; color: #6e7781; }
            .firma { margin-top: 22px; font-size: 9px; }
            .firma td { padding-top: 26px; border-top: 1px solid #1f2328; width: 45%; }
        </style>
        HTML;
    }

    private function encabezado(): string
    {
        return <<<'HTML'
        <div class="encabezado">
            <h1>FORMULARIO UNICO DE REGISTRO (FUR)</h1>
            <p class="organismo">Instituto Correntino de Aguas y Arroyos (ICAA)</p>
            <p>Plataforma de Gestion de Efluentes - Documento generado automaticamente</p>
        </div>
        HTML;
    }

    private function bloqueEstablecimiento(Establecimiento $establecimiento): string
    {
        $filas = [
            'Razon Social' => $establecimiento->nombre,
            'CUIT' => $establecimiento->cuit,
            'Tipo de Negocio' => self::RUBROS[$establecimiento->rubro] ?? $establecimiento->rubro,
            'Ubicacion' => $establecimiento->ubicacion,
            'Coordenadas' => $this->coordenadas($establecimiento),
            'Capacidad Maxima (personas)' => $establecimiento->capacidad_maxima,
            'Capacidad Biodigestor (L)' => $establecimiento->capacidad_biodigestor,
            'Responsable' => $establecimiento->user
                ? trim($establecimiento->user->nombre.' '.$establecimiento->user->apellido)
                : null,
            'DNI del Responsable' => $establecimiento->user?->dni,
            'Telefono' => $establecimiento->user?->telefono,
            'Destino Actual del Vuelco' => self::DESTINOS[$establecimiento->tipo_destino_vuelco] ?? $establecimiento->tipo_destino_vuelco,
        ];

        return $this->bloque('Datos del Establecimiento', $this->tablaDatos($filas));
    }

    /**
     * Muestra el permiso vigente, que puede tener un destino distinto al actual.
     */
    private function bloquePermiso(Establecimiento $establecimiento): string
    {
        $permiso = $establecimiento->permisoVuelco;

        if ($permiso === null) {
            return $this->bloque(
                'Permiso de Vuelco',
                '<p style="color:#b02a37;margin:0">El establecimiento no tiene permiso de vuelco registrado.</p>'
            );
        }

        $filas = [
            'Numero de Expediente' => $permiso->numero_expediente,
            'Estado' => $permiso->estado,
            'Fecha de Emision' => $permiso->fecha_emision?->format('d/m/Y'),
            'Fecha de Vencimiento' => $permiso->fecha_vencimiento?->format('d/m/Y'),
            'Destino Fijado en el Permiso' => self::DESTINOS[$permiso->tipo_destino_vuelco] ?? $permiso->tipo_destino_vuelco,
        ];

        return $this->bloque('Permiso de Vuelco', $this->tablaDatos($filas));
    }

    /**
     * @param  array{total: int, cumple: int, alerta: int, incumple: int, porcentaje_cumplimiento: int, meses_consecutivos_cumple: int, obtiene_insignia: bool}  $estadisticas
     */
    private function bloqueEstadisticas(array $estadisticas): string
    {
        $celdas = [
            "{$estadisticas['porcentaje_cumplimiento']}%<br><span class='leyenda'>Cumplimiento global</span>",
            "{$estadisticas['cumple']} / {$estadisticas['total']}<br><span class='leyenda'>Analisis aprobados</span>",
            "{$estadisticas['meses_consecutivos_cumple']}<br><span class='leyenda'>Meses consecutivos en verde</span>",
        ];

        $tabla = '<table class="indicadores"><tr>'
            .implode('', array_map(fn (string $celda): string => "<td>{$celda}</td>", $celdas))
            .'</tr></table>';

        return $this->bloque('Estadisticas de Cumplimiento', $tabla);
    }

    /**
     * @param  array{meses_consecutivos_cumple: int}  $estadisticas
     */
    private function bloqueInsignia(array $estadisticas): string
    {
        return <<<HTML
        <div class="insignia">
            <div class="titulo">ESTABLECIMIENTO RECOMENDADO</div>
            <div class="detalle">{$estadisticas['meses_consecutivos_cumple']} meses consecutivos sin incumplimientos la Resolucion ICAA 312/21</div>
        </div>
        HTML;
    }

    /**
     * @param  Collection<int, AnalisisLaboratorio>  $analisis
     * @param  array{total: int}  $estadisticas
     */
    private function bloqueAnalisis(Collection $analisis, array $estadisticas): string
    {
        $filas = '';

        foreach ($analisis as $registro) {
            $semaforo = self::SEMAFORO[$registro->resultado_final]
                ?? ['color' => '#57606a', 'texto' => $registro->resultado_final];

            $badge = sprintf(
                '<span style="background-color:%s;color:#fff;padding:2px 6px;border-radius:2px;">%s</span>',
                $semaforo['color'],
                e($semaforo['texto'])
            );

            $filas .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                e($registro->fecha_muestra->format('d/m/Y')),
                e($registro->laboratorio),
                $badge,
                $registro->parametros_count > 0
                    ? $registro->parametros_count.' parametros'
                    : 'sin parametros'
            );
        }

        if ($filas === '') {
            $filas = '<tr><td colspan="4">El establecimiento aun no registro analisis de laboratorio.</td></tr>';
        }

        $tabla = <<<HTML
        <table class="grilla">
            <thead><tr><th>Fecha de Muestra</th><th>Laboratorio</th><th>Resultado</th><th>Parametros</th></tr></thead>
            <tbody>{$filas}</tbody>
        </table>
        HTML;

        return $this->bloque("Historial de Analisis ({$estadisticas['total']} registros)", $tabla);
    }

    private function pieDePagina(): string
    {
        $generado = Carbon::now('America/Argentina/Buenos_Aires')->format('d/m/Y H:i:s');

        return <<<HTML
        <table class="firma">
            <tr>
                <td>Firma y aclaracion del responsable legal</td>
                <td>Recepcion ICAA (fecha y sello)</td>
            </tr>
        </table>
        <div class="pie">
            <p>Este documento es un borrador generado automaticamente por la Plataforma de Gestion de Efluentes.</p>
            <p>Debe ser revisado, impreso y firmado por el representante legal antes de presentarse ante el ICAA.</p>
            <p>Generado el {$generado}</p>
        </div>
        HTML;
    }

    private function coordenadas(Establecimiento $establecimiento): string
    {
        if ($establecimiento->latitud === null || $establecimiento->longitud === null) {
            return 'Sin georreferenciar';
        }

        return $establecimiento->latitud.', '.$establecimiento->longitud;
    }

    /**
     * Envuelve contenido en un bloque con título.
     */
    private function bloque(string $titulo, string $contenido): string
    {
        return "<div class='bloque'><h2>".e($titulo)."</h2>{$contenido}</div>";
    }

    /**
     * Arma una tabla de etiqueta/valor descartando los valores nulos.
     *
     * @param  array<string, mixed>  $filas
     */
    private function tablaDatos(array $filas): string
    {
        $html = '<table class="datos">';

        foreach ($filas as $etiqueta => $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }

            $html .= sprintf(
                '<tr><td class="etiqueta">%s</td><td>%s</td></tr>',
                e($etiqueta),
                e((string) $valor)
            );
        }

        return $html.'</table>';
    }
}
