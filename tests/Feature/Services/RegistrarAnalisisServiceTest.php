<?php

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\LimiteEfluente;
use App\Models\User;
use App\Services\RegistrarAnalisisService;
use Dompdf\Dompdf;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Construye un informe PDF real con Dompdf, igual que la suite del extractor.
 */
function informeParaRegistrar(string $contenido): string
{
    $dompdf = new Dompdf;
    $dompdf->loadHtml(
        '<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif}</style></head>'
        .'<body>'.$contenido.'</body></html>'
    );
    $dompdf->render();

    return $dompdf->output();
}

/**
 * Genera un PDF con solo una imagen, como un escaneo sin capa de texto.
 */
function escaneoParaRegistrar(): string
{
    $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    $dompdf = new Dompdf;
    $dompdf->loadHtml('<html><body><img src="'.$png.'"></body></html>');
    $dompdf->render();

    return $dompdf->output();
}

/**
 * Guarda el PDF en disco porque el servicio recibe la ruta ya almacenada.
 */
function guardarInformeParaRegistrar(string $pdf): string
{
    $ruta = sys_get_temp_dir().'/informe-registrar-'.uniqid().'.pdf';
    file_put_contents($ruta, $pdf);

    return $ruta;
}

/**
 * @param  array<string, mixed>  $atributos
 */
function establecimientoParaRegistrar(array $atributos = []): Establecimiento
{
    return Establecimiento::create(array_merge([
        'user_id' => User::factory()->create()->getKey(),
        'nombre' => 'Hotel Costa del Iberá',
        'rubro' => 'hotel',
        'latitud' => -27.3667,
        'longitud' => -55.8961,
        'capacidad_maxima' => 40,
        'capacidad_biodigestor' => 5000,
        'tipo_destino_vuelco' => 'cursos_agua',
    ], $atributos));
}

test('registra el analisis evaluado con sus parametros y sincroniza los limites de la norma', function () {
    LimiteEfluente::factory()->create(['parametro' => 'Temperatura', 'cursos_agua' => 'Hasta 35']);
    LimiteEfluente::factory()->create(['parametro' => 'pH', 'cursos_agua' => '6,5-8,5']);
    $establecimiento = establecimientoParaRegistrar();
    $ruta = guardarInformeParaRegistrar(informeParaRegistrar(<<<'HTML'
    <p>Laboratorio Ambiental de Prueba S.R.L.</p>
    <p>Fecha de muestra: 28/09/2026</p>
    <p>Temperatura: 25 °C</p>
    <p>pH: 7,1 UpH</p>
    <p>DBO5: 42 mg/lts</p>
    HTML));

    $resultado = app(RegistrarAnalisisService::class)->registrar($establecimiento, $ruta);
    $analisis = $resultado['analisis'];

    expect($analisis)->toBeInstanceOf(AnalisisLaboratorio::class)
        ->and($analisis->establecimiento_id)->toBe($establecimiento->getKey())
        ->and($analisis->resultado_final)->toBe('Pendiente')
        ->and($analisis->estado)->toBe('evaluado')
        ->and($analisis->resultado_sugerido)->toBe('cumple')
        ->and($analisis->ruta_pdf)->toBe($ruta)
        ->and($analisis->fecha_muestra->toDateString())->toBe('2026-09-28')
        ->and($analisis->laboratorio)->toContain('Laboratorio Ambiental de Prueba')
        ->and($resultado['advertencias'])->toBeEmpty();

    expect($analisis->parametros)->toHaveCount(3);

    $parametros = $analisis->parametros->keyBy('nombre');

    expect($parametros['Temperatura']->valor_medido)->toBe('25')
        ->and($parametros['Temperatura']->unidad)->toBe('°C')
        ->and($parametros['Temperatura']->detectado_por)->toBe('temperatura')
        ->and($parametros['pH']->valor_medido)->toBe('7.1')
        ->and($parametros['Demanda Bioquímica de Oxígeno (DBO5)']->detectado_por)->toBe('DBO5');

    // Solo se asocian los límites de parámetros presentes en el informe y en la
    // norma: DBO5 no tiene fila en limite_efluente, así que queda fuera del pivote.
    expect($analisis->limitesEfluentes->pluck('parametro')->all())
        ->toEqualCanonicalizing(['pH', 'Temperatura']);
});

test('persiste el analisis como observado cuando el pdf no tiene capa de texto', function () {
    $establecimiento = establecimientoParaRegistrar();
    $ruta = guardarInformeParaRegistrar(escaneoParaRegistrar());

    $resultado = app(RegistrarAnalisisService::class)->registrar($establecimiento, $ruta);
    $analisis = $resultado['analisis'];

    expect($analisis->estado)->toBe('observado')
        ->and($analisis->resultado_final)->toBe('Pendiente')
        ->and($analisis->resultado_sugerido)->toBeNull()
        ->and($analisis->ruta_pdf)->toBe($ruta)
        ->and($analisis->fecha_muestra->toDateString())->toBe(now()->toDateString())
        ->and($analisis->laboratorio)->toBe('No determinado')
        ->and($analisis->parametros)->toBeEmpty()
        ->and($analisis->limitesEfluentes)->toBeEmpty()
        ->and($resultado['advertencias'])->toContain(
            'pdf_sin_capa_texto: el informe parece una imagen escaneada y requiere OCR previo',
            'laboratorio_no_identificado: se registra el análisis sin nombre de laboratorio.'
        );
});

test('une las advertencias del evaluador con las del extractor y usa la fecha de hoy de respaldo', function () {
    LimiteEfluente::factory()->create(['parametro' => 'pH', 'cursos_agua' => '6,5-8,5']);
    $establecimiento = establecimientoParaRegistrar(['tipo_destino_vuelco' => null]);
    $ruta = guardarInformeParaRegistrar(informeParaRegistrar(
        '<p>Informe de laboratorio ambiental</p><p>pH: 7,1 UpH</p>'
    ));

    $resultado = app(RegistrarAnalisisService::class)->registrar($establecimiento, $ruta);
    $analisis = $resultado['analisis'];

    expect($analisis->estado)->toBe('evaluado')
        ->and($analisis->fecha_muestra->toDateString())->toBe(now()->toDateString())
        ->and($resultado['advertencias'])->toContain(
            'no_se_reconocio_la_fecha_de_muestra',
            'destino_desconocido: null. Se aplican los límites de cursos_agua.'
        );
});
