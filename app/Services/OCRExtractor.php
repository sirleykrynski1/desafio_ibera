<?php

namespace App\Services;

use Carbon\Carbon;
use Smalot\PdfParser\Parser;

/**
 * OCRExtractor
 *
 * Responsabilidad: Leer un informe de laboratorio en PDF y devolver sus
 * mediciones ya normalizadas y con los nombres canónicos de la tabla
 * `limite_efluente`, listos para crear registros en `parametro_analisis`.
 *
 * Entrada: un PDF (ruta en disco o contenido binario)
 * Salida: array con los datos del informe y las advertencias del proceso
 *
 * IMPORTANTE: este servicio NO hace OCR real. Lee la capa de texto del PDF.
 * Si el informe fue escaneado como imagen no va a encontrar nada y lo avisa
 * con la advertencia `pdf_sin_capa_texto`; en ese caso hay que pasar el
 * archivo por Tesseract u otro OCR antes de llamar a este servicio.
 */
class OCRExtractor
{
    /**
     * Mapea cada parámetro canónico de `limite_efluente` con las variantes
     * con las que los laboratorios suelen escribirlo y con su unidad oficial.
     *
     * La clave del array es el valor exacto de `limite_efluente.parametro`,
     * para que el llamador pueda resolver el límite con una consulta directa.
     *
     * @var array<string, array{unidad: string, etiquetas: list<string>}>
     */
    private const PARAMETROS = [
        'Temperatura' => [
            'unidad' => '°C',
            'etiquetas' => ['temperatura', 'teperatura'],
        ],
        'pH' => [
            'unidad' => 'UpH',
            'etiquetas' => ['pH'],
        ],
        'Sólidos Sedimentables (2 horas)' => [
            'unidad' => 'ml/lts',
            'etiquetas' => ['sólidos sedimentables', 'Sólidos Sedimentables 2 horas', 'solidos sedimentables', 'sólidos en suspensión', 'solidos en suspension', 'SS'],
        ],
        'Demanda Bioquímica de Oxígeno (DBO5)' => [
            'unidad' => 'mg/lts',
            'etiquetas' => ['DBO5', 'DBO₅', 'Demanda Bioquímica de Oxígeno', 'Demanda Bioquimica de Oxigeno'],
        ],
        'Demanda Química de Oxígeno (DQO)' => [
            'unidad' => 'mg/lts',
            'etiquetas' => ['DQO', 'Demanda Química de Oxígeno', 'Demanda Quimica de Oxigeno'],
        ],
        'Sustancias Fenólicas' => [
            'unidad' => 'mg/lts',
            'etiquetas' => ['sustancias fenólicas', 'sustancias fenolicas', 'fenoles'],
        ],
        'Hidrocarburos Totales' => [
            'unidad' => 'mg/lts',
            'etiquetas' => ['hidrocarburos totales', 'hidrocarburos'],
        ],
        'Coliformes Fecales' => [
            'unidad' => 'NMP/100ml',
            'etiquetas' => ['coliformes fecales', 'coliformes totales'],
        ],
        'Plomo' => [
            'unidad' => 'mg/lts',
            'etiquetas' => ['plomo', 'Pb'],
        ],
        'Cloro Libre Residual' => [
            'unidad' => 'mg/lts',
            'etiquetas' => ['cloro libre residual', 'cloro residual'],
        ],
    ];

    /**
     * Etiquetas que encabezan la fecha de toma de muestra en los informes.
     *
     * @var list<string>
     */
    private const ETIQUETAS_FECHA = [
        'fecha de muestra',
        'fecha de toma',
        'fecha muestreo',
        'fecha de análisis',
        'fecha de analisis',
        'fecha',
        'fecha ingreso muestra',
        'fecha analisis',
    ];

    /**
     * Meses en español para fechas escritas con letras.
     *
     * @var array<string, int>
     */
    private const MESES = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6,
        'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
        'noviembre' => 11, 'diciembre' => 12,
    ];

    /**
     * Texto mínimo para considerar que un PDF tiene capa de texto utilizable.
     */
    private const MINIMO_CARACTERES = 20;

    /**
     * Extrae los datos de un informe guardado en disco.
     *
     * @return array{laboratorio: ?string, fecha_muestra: ?string, parametros: list<array{nombre: string, valor_medido: string, unidad: string, detectado_por: string}>, tiene_capa_texto: bool, advertencias: list<string>}
     */
    public function extraerDesdeRuta(string $ruta): array
    {
        if (! is_readable($ruta)) {
            return $this->resultadoVacio(['archivo_no_legible: '.$ruta]);
        }

        try {
            $texto = (new Parser)->parseFile($ruta)->getText();
        } catch (\Throwable $e) {
            return $this->resultadoVacio(['pdf_ilegible: '.$e->getMessage()]);
        }

        return $this->interpretar($texto);
    }

    /**
     * Extrae los datos de un PDF recibido como contenido binario.
     *
     * @return array{laboratorio: ?string, fecha_muestra: ?string, parametros: list<array{nombre: string, valor_medido: string, unidad: string, detectado_por: string}>, tiene_capa_texto: bool, advertencias: list<string>}
     */
    public function extraerDesdeContenido(string $contenido): array
    {
        try {
            $texto = (new Parser)->parseContent($contenido)->getText();
        } catch (\Throwable $e) {
            return $this->resultadoVacio(['pdf_ilegible: '.$e->getMessage()]);
        }

        return $this->interpretar($texto);
    }

    /**
     * Interpreta el texto plano del informe.
     *
     * @return array{laboratorio: ?string, fecha_muestra: ?string, parametros: list<array{nombre: string, valor_medido: string, unidad: string, detectado_por: string}>, tiene_capa_texto: bool, advertencias: list<string>}
     */
    private function interpretar(string $texto): array
    {
        $advertencias = [];
        $texto = $this->normalizar($texto);

        if (mb_strlen($texto) < self::MINIMO_CARACTERES) {
            return $this->resultadoVacio([
                'pdf_sin_capa_texto: el informe parece una imagen escaneada y requiere OCR previo',
            ]);
        }

        $parametros = $this->extraerParametros($texto, $advertencias);
        $laboratorio = $this->extraerLaboratorio($texto);
        $fechaMuestra = $this->extraerFechaMuestra($texto);

        if ($parametros === []) {
            $advertencias[] = 'no_se_reconocieron_parametros_conocidos';
        }

        if ($fechaMuestra === null) {
            $advertencias[] = 'no_se_reconocio_la_fecha_de_muestra';
        }

        return [
            'laboratorio' => $laboratorio,
            'fecha_muestra' => $fechaMuestra,
            'parametros' => $parametros,
            'tiene_capa_texto' => true,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Colapsa los espacios que los generadores de PDF suelen fragmentar.
     */
    private function normalizar(string $texto): string
    {
        $texto = str_replace("\u{00A0}", ' ', $texto);

        return trim(preg_replace('/[ \t]+/u', ' ', $texto) ?? $texto);
    }

    /**
     * Busca cada parámetro conocido y normaliza su medición.
     *
     * @param  list<string>  $advertencias
     * @return list<array{nombre: string, valor_medido: string, unidad: string, detectado_por: string}>
     */
    private function extraerParametros(string $texto, array &$advertencias): array
    {
        $encontrados = [];

        foreach (self::PARAMETROS as $canonico => $definicion) {
            foreach ($definicion['etiquetas'] as $etiqueta) {
                $patron = '/'.preg_quote($etiqueta, '/')
                    .'[^\d\r\n]{0,20}?\(*\s*[<≤]?\s*(?<valor>\d{1,3}(?:[.,\s]\d{3})*(?:[.,]\d+)?)\s*(?<unidad>[a-zA-Z\/°µ%]{1,12})?/ui';

                if (preg_match($patron, $texto, $coincidencias) !== 1) {
                    continue;
                }

                $valor = $this->normalizarNumero($coincidencias['valor']);

                if ($valor === null) {
                    $advertencias[] = "valor_no_numerico_en_{$canonico}";

                    continue;
                }

                $encontrados[$canonico] = [
                    'nombre' => $canonico,
                    'valor_medido' => $valor,
                    'unidad' => $definicion['unidad'],
                    'detectado_por' => $etiqueta,
                ];

                break;
            }
        }

        return array_values($encontrados);
    }

    /**
     * Convierte el número tal como lo escribe el laboratorio al formato con punto.
     *
     * Los informes argentinos usan coma decimal y punto de miles ("1.250,5"),
     * que es justo lo contrario al formato de PHP, así que hay que distinguir
     * el separador decimal del de miles antes de convertir.
     */
    private function normalizarNumero(string $numero): ?string
    {
        $limpio = preg_replace('/\s+/', '', $numero) ?? $numero;

        $tieneComa = str_contains($limpio, ',');
        $tienePunto = str_contains($limpio, '.');

        if ($tieneComa && $tienePunto) {
            // El último separador que aparece es el decimal.
            if (strrpos($limpio, ',') > strrpos($limpio, '.')) {
                $limpio = str_replace('.', '', $limpio);
                $limpio = str_replace(',', '.', $limpio);
            } else {
                $limpio = str_replace(',', '', $limpio);
            }
        } elseif ($tieneComa) {
            $limpio = str_replace(',', '.', $limpio);
        }

        return is_numeric($limpio) ? (string) (float) $limpio : null;
    }

    /**
     * Recupera el nombre del laboratorio que emitió el informe.
     *
     * Se captura la línea completa desde la palabra clave en lugar de intentar
     * separar un calificador, porque "Laboratorio Ambiental de Prueba S.R.L."
     * forma un único nombre y "ambiental" no es un separador sino parte de él.
     */
    private function extraerLaboratorio(string $texto): ?string
    {
        return preg_match('/laboratorio[^\r\n]{2,80}/ui', $texto, $coincidencias) === 1
            ? $this->limpiarValor($coincidencias[0])
            : null;
    }

    /**
     * Palabras que indican que una fecha NO es la de toma de muestra.
     */
    private const FECHAS_A_EXCLUIR = [
        'emisión', 'emision', 'emisión del informe', 'informe', 'reporte',
        'vencimiento', 'caducidad', 'entrega', 'recepción', 'recepcion',
    ];

    /**
     * Recupera la fecha de toma de muestra en cualquiera de los formatos habituales.
     *
     * Se intenta primero junto a una etiqueta ("Fecha de muestra: ...") y solo
     * después se acepta cualquier fecha del documento, para no tomar por
     * muestra la fecha de emisión del informe.
     */
    private function extraerFechaMuestra(string $texto): ?string
    {
        $etiquetas = implode('|', array_map(
            fn (string $etiqueta): string => preg_quote($etiqueta, '/'),
            self::ETIQUETAS_FECHA
        ));

        $meses = implode('|', array_keys(self::MESES));

        // Los patrones van sin delimitadores porque se les antepone el
        // fragmento de la etiqueta, que forma parte de la misma expresión.
        // El formato ISO va primero a propósito: "2026-03-15" contiene un tramo
        // que también encaja con dd-mm-aaa y se leería como 15/03/2026.
        $patrones = [
            '(?<a>\d{4})[\/\-.](?<m>\d{1,2})[\/\-.](?<d>\d{1,2})',
            '(?<d>\d{1,2})[\/\-.](?<m>\d{1,2})[\/\-.](?<a>\d{2,4})',
            '(?<d>\d{1,2})\s*(?:de\s+)?(?<mes>'.$meses.')\s*(?:de\s+)?(?<a>\d{4})',
        ];

        foreach ([true, false] as $exigirEtiqueta) {
            $prefijo = $exigirEtiqueta
                ? '[^\r\n]{0,30}?'.$etiquetas.'[^\r\n]{0,30}?'
                : '';

            foreach ($patrones as $cuerpo) {
                preg_match_all('/'.$prefijo.$cuerpo.'/iu', $texto, $coincidencias, PREG_OFFSET_CAPTURE);

                // Se recorren todas las coincidencias porque la fecha válida
                // puede estar después de una fecha de emisión que hay que saltear.
                foreach ($coincidencias[0] ?? [] as $indice => $encontrada) {
                    if ($this->esFechaNoMuestra($this->lineaEn($texto, (int) $encontrada[1]))) {
                        continue;
                    }

                    // El patrón con el mes escrito define "mes" en lugar de "m".
                    $mes = isset($coincidencias['m'][$indice])
                        ? (int) $coincidencias['m'][$indice][0]
                        : (self::MESES[strtolower($coincidencias['mes'][$indice][0] ?? '')] ?? 0);

                    $fecha = $this->construirFecha(
                        (int) ($coincidencias['d'][$indice][0] ?? 0),
                        $mes,
                        (int) ($coincidencias['a'][$indice][0] ?? 0)
                    );

                    if ($fecha !== null) {
                        return $fecha;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Indica si la línea contiene una fecha que no es la de toma de muestra.
     */
    private function esFechaNoMuestra(string $linea): bool
    {
        return preg_match('/'.implode('|', self::FECHAS_A_EXCLUIR).'/iu', $linea) === 1;
    }

    /**
     * Devuelve la línea del documento que contiene la posición indicada.
     */
    private function lineaEn(string $texto, int $posicion): string
    {
        $previa = substr($texto, 0, $posicion);
        $inicio = strrpos($previa, "\n");
        $inicio = $inicio === false ? 0 : $inicio + 1;
        $fin = strpos($texto, "\n", $posicion);

        return substr($texto, $inicio, ($fin === false ? strlen($texto) : $fin) - $inicio);
    }

    /**
     * Valida y normaliza una fecha a formato Y-m-d.
     */
    private function construirFecha(int $dia, int $mes, int $anio): ?string
    {
        if ($anio < 100) {
            $anio += $anio > 70 ? 1900 : 2000;
        }

        if (! checkdate($mes, $dia, $anio)) {
            return null;
        }

        return Carbon::create($anio, $mes, $dia)->format('Y-m-d');
    }

    /**
     * Quita el ruido que arrastran los bordes de un PDF.
     */
    private function limpiarValor(string $valor): ?string
    {
        $limpio = trim(preg_replace('/[^\p{L}\p{N}\s\.\-\/()]/u', ' ', $valor) ?? $valor);

        return $limpio === '' ? null : $limpio;
    }

    /**
     * @param  list<string>  $advertencias
     * @return array{laboratorio: null, fecha_muestra: null, parametros: list<array{nombre: string, valor_medido: string, unidad: string, detectado_por: string}>, tiene_capa_texto: bool, advertencias: list<string>}
     */
    private function resultadoVacio(array $advertencias): array
    {
        return [
            'laboratorio' => null,
            'fecha_muestra' => null,
            'parametros' => [],
            'tiene_capa_texto' => false,
            'advertencias' => $advertencias,
        ];
    }
}
