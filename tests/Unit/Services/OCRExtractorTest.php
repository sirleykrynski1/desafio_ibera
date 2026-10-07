<?php

use App\Services\OCRExtractor;
use Dompdf\Dompdf;

/**
 * Construye un informe PDF real para no tener que depender de un binario
 * externo. Se usa Dompdf en crudo y no la facade de Laravel para que este
 * test siga siendo unitario y no necesite el contenedor de la aplicación.
 */
function informeEnPdf(string $contenido): string
{
    $dompdf = new Dompdf;
    $dompdf->loadHtml(
        '<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif}</style></head>'
        .'<body>'.$contenido.'</body></html>'
    );
    $dompdf->render();

    return $dompdf->output();
}

test('extrae los parametros de un informe de laboratorio en pdf', function () {
    $pdf = informeEnPdf(<<<'HTML'
    <p>Laboratorio Ambiental de Prueba S.R.L.</p>
    <p>Fecha de muestra: 28/09/2026</p>
    <table>
        <tr><td>Temperatura</td><td>38,5 °C</td></tr>
        <tr><td>pH</td><td>7,1 UpH</td></tr>
        <tr><td>DBO5</td><td>42 mg/lts</td></tr>
        <tr><td>DQO</td><td>180 mg/lts</td></tr>
        <tr><td>Hidrocarburos Totales</td><td>6 mg/lts</td></tr>
    </table>
    HTML);

    $resultado = (new OCRExtractor)->extraerDesdeContenido($pdf);

    expect($resultado['tiene_capa_texto'])->toBeTrue()
        ->and($resultado['advertencias'])->toBeEmpty()
        ->and($resultado['fecha_muestra'])->toBe('2026-09-28')
        ->and($resultado['laboratorio'])->toContain('Laboratorio Ambiental de Prueba')
        ->and($resultado['parametros'])->toHaveCount(5);

    $valores = collect($resultado['parametros'])->keyBy('nombre');

    expect($valores['Temperatura']['valor_medido'])->toBe('38.5')
        ->and($valores['Temperatura']['unidad'])->toBe('°C')
        ->and($valores['pH']['valor_medido'])->toBe('7.1')
        ->and($valores['Demanda Bioquímica de Oxígeno (DBO5)']['valor_medido'])->toBe('42')
        ->and($valores['Demanda Química de Oxígeno (DQO)']['valor_medido'])->toBe('180')
        ->and($valores['Hidrocarburos Totales']['valor_medido'])->toBe('6');
});

test('devuelve los nombres canonicos que espera la tabla limite_efluente', function () {
    $pdf = informeEnPdf('<p>Sustancias Fenólicas: 12 mg/lts</p><p>Coliformes Fecales: 1.000 NMP/100ml</p>');

    $nombres = collect((new OCRExtractor)->extraerDesdeContenido($pdf)['parametros'])
        ->pluck('nombre')
        ->all();

    expect($nombres)->toBe(['Sustancias Fenólicas', 'Coliformes Fecales']);
});

test('normaliza la coma decimal y el punto de miles al formato de php', function () {
    $pdf = informeEnPdf('<p>Temperatura: 1.250,75 °C</p><p>pH: 6,25</p>');

    $valores = collect((new OCRExtractor)->extraerDesdeContenido($pdf)['parametros'])->keyBy('nombre');

    expect($valores['Temperatura']['valor_medido'])->toBe('1250.75')
        ->and($valores['pH']['valor_medido'])->toBe('6.25');
});

test('acepta fechas en formato iso y con el mes escrito', function () {
    $iso = informeEnPdf('<p>Fecha de toma: 2026-03-15</p><p>pH: 7</p>');
    $texto = informeEnPdf('<p>Fecha de muestra: 15 de marzo de 2026</p><p>pH: 7</p>');

    expect((new OCRExtractor)->extraerDesdeContenido($iso)['fecha_muestra'])->toBe('2026-03-15')
        ->and((new OCRExtractor)->extraerDesdeContenido($texto)['fecha_muestra'])->toBe('2026-03-15');
});

test('toma la fecha de muestra y no la de emision del informe', function () {
    $pdf = informeEnPdf(<<<'HTML'
    <p>Fecha de emisión: 02/10/2026</p>
    <p>Fecha de muestra: 28/09/2026</p>
    HTML);

    expect((new OCRExtractor)->extraerDesdeContenido($pdf)['fecha_muestra'])->toBe('2026-09-28');
});

test('avisa cuando el pdf es solo una imagen escaneada y no devuelve datos vacios en silencio', function () {
    // Un PNG de 1x1 embebido: el PDF resultante tiene capa de imagen pero
    // ningún texto, que es exactamente lo que produce un escaneo.
    $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    $dompdf = new Dompdf;
    $dompdf->loadHtml('<html><body><img src="'.$png.'"></body></html>');
    $dompdf->render();

    $resultado = (new OCRExtractor)->extraerDesdeContenido($dompdf->output());

    expect($resultado['tiene_capa_texto'])->toBeFalse()
        ->and($resultado['parametros'])->toBeEmpty()
        ->and($resultado['advertencias'])->toContain(
            'pdf_sin_capa_texto: el informe parece una imagen escaneada y requiere OCR previo'
        );
});

test('avisa en vez de fallar cuando el archivo no es un pdf valido', function () {
    $resultado = (new OCRExtractor)->extraerDesdeContenido('esto no es un pdf');

    expect($resultado['tiene_capa_texto'])->toBeFalse()
        ->and($resultado['parametros'])->toBeEmpty()
        ->and($resultado['advertencias'])->not->toBeEmpty();
});

test('avisa cuando la ruta no existe o no se puede leer', function () {
    $resultado = (new OCRExtractor)->extraerDesdeRuta(sys_get_temp_dir().'/informe-inexistente.pdf');

    expect($resultado['tiene_capa_texto'])->toBeFalse()
        ->and($resultado['advertencias'][0])->toStartWith('archivo_no_legible:');
});

test('no confunde un valor de otro parametro con el que se busca', function () {
    // El informe menciona "DQO" y "DBO5" juntos: cada uno debe tomar su propio número.
    $pdf = informeEnPdf('<p>DBO5: 42 mg/lts</p><p>DQO: 265 mg/lts</p>');

    $valores = collect((new OCRExtractor)->extraerDesdeContenido($pdf)['parametros'])->keyBy('nombre');

    expect($valores['Demanda Bioquímica de Oxígeno (DBO5)']['valor_medido'])->toBe('42')
        ->and($valores['Demanda Química de Oxígeno (DQO)']['valor_medido'])->toBe('265');
});
