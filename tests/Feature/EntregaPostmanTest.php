<?php

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\User;
use Database\Seeders\LimiteEfluenteSeeder;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('la colección recorre el backend por HTTP real con cookies CSRF PDF y dictamen', function () {
    $newman = getenv('IBERA_NEWMAN_CLI');
    $node = getenv('IBERA_NODE_BINARY');
    if (! $newman || ! $node) {
        $this->markTestSkipped('Prueba optativa: indicar IBERA_NEWMAN_CLI e IBERA_NODE_BINARY.');
    }
    expect(is_file($newman))->toBeTrue();
    expect(is_file($node))->toBeTrue();

    File::ensureDirectoryExists(storage_path('framework/testing'));
    $raiz = realpath(storage_path('framework/testing'));
    $directorio = $raiz.DIRECTORY_SEPARATOR.'entrega-'.Str::uuid();
    File::ensureDirectoryExists($directorio);
    $servidor = null;

    try {
        $base = $directorio.DIRECTORY_SEPARATOR.'pruebas.sqlite';
        touch($base);
        config([
            'database.default' => 'entrega',
            'database.connections.entrega' => [
                'driver' => 'sqlite', 'database' => $base, 'prefix' => '', 'foreign_key_constraints' => true,
            ],
        ]);
        $this->artisan('migrate', ['--database' => 'entrega', '--force' => true, '--no-interaction' => true])->assertSuccessful();
        $this->seed(LimiteEfluenteSeeder::class);
        $clave = Str::random(24);
        $propietario = User::factory()->create(['email' => 'propietario@example.test', 'password' => $clave]);
        $inspector = User::factory()->create(['email' => 'inspector@example.test', 'password' => $clave, 'rol' => 'inspector']);

        $pdf = new Dompdf;
        $pdf->loadHtml('<html><body><p>INFORME FICTICIO</p><p>Laboratorio de Prueba</p><p>Temperatura: 25 C</p><p>pH: 7.1</p></body></html>');
        $pdf->render();
        File::put($directorio.DIRECTORY_SEPARATOR.'informe.pdf', $pdf->output());
        foreach (['framework/views', 'framework/sessions', 'framework/cache/data', 'app/private', 'logs'] as $carpeta) {
            File::ensureDirectoryExists($directorio.DIRECTORY_SEPARATOR.$carpeta);
        }

        $socket = stream_socket_server('tcp://127.0.0.1:0');
        expect($socket)->not->toBeFalse();
        $direccion = stream_socket_get_name($socket, false);
        fclose($socket);
        $url = 'http://'.$direccion;
        $entorno = [
            'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_URL' => $url,
            'APP_KEY' => config('app.key'), 'APP_CONFIG_CACHE' => $directorio.'/config.php',
            'APP_ROUTES_CACHE' => $directorio.'/routes.php', 'LARAVEL_STORAGE_PATH' => $directorio,
            'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $base, 'DB_URL' => '',
            'CACHE_STORE' => 'database', 'SESSION_DRIVER' => 'database', 'SESSION_COOKIE' => 'ibera_entrega',
            'SESSION_DOMAIN' => 'null', 'SESSION_SECURE_COOKIE' => 'false',
            'QUEUE_CONNECTION' => 'sync', 'LOG_CHANNEL' => 'single',
            'CLIMATE_API_URL' => 'http://127.0.0.1:1', 'CLIMATE_API_CONNECT_TIMEOUT' => '1',
            'CLIMATE_API_TIMEOUT' => '1',
        ];
        $router = $directorio.DIRECTORY_SEPARATOR.'router.php';
        File::put($router, '<?php define("LARAVEL_START", microtime(true)); '
            .'require '.var_export(base_path('vendor/autoload.php'), true).'; '
            .'$app = require '.var_export(base_path('bootstrap/app.php'), true).'; '
            .'$app->useStoragePath('.var_export($directorio, true).'); '
            .'$app->handleRequest(\Illuminate\Http\Request::capture());');
        $servidor = new Process([
            PHP_BINARY, '-S', $direccion, $router,
        ], public_path(), $entorno);
        $servidor->setTimeout(15);
        $servidor->start();
        expect($servidor->waitUntil(fn (string $tipo, string $salida): bool => str_contains($salida, 'Development Server')))->toBeTrue();
        $servidor->setTimeout(null);

        $coleccion = json_decode(File::get(base_path('Desafio-Ibera.postman_collection.json')), true, 512, JSON_THROW_ON_ERROR);
        $coleccion['item'] = array_slice($coleccion['item'], 0, 3);
        $variables = [
            'base_url' => $url, 'propietario_email' => $propietario->email, 'propietario_password' => $clave,
            'inspector_email' => $inspector->email, 'inspector_password' => $clave,
        ];
        foreach ($coleccion['variable'] as &$variable) {
            $variable['value'] = $variables[$variable['key']] ?? $variable['value'];
        }
        unset($variable);
        foreach ($coleccion['item'] as &$grupo) {
            foreach ($grupo['item'] as &$solicitud) {
                if (($solicitud['request']['body']['mode'] ?? null) === 'formdata') {
                    foreach ($solicitud['request']['body']['formdata'] as &$campo) {
                        if ($campo['type'] === 'file') {
                            $campo['src'] = 'informe.pdf';
                        }
                    }
                    unset($campo);
                }
            }
            unset($solicitud);
        }
        unset($grupo);
        $archivoColeccion = $directorio.DIRECTORY_SEPARATOR.'coleccion.json';
        $archivoReporte = $directorio.DIRECTORY_SEPARATOR.'reporte.json';
        File::put($archivoColeccion, json_encode($coleccion, JSON_THROW_ON_ERROR));
        $ejecucion = new Process([
            $node, $newman, 'run', $archivoColeccion, '--working-dir', $directorio,
            '--reporters', 'json', '--reporter-json-export', $archivoReporte, '--timeout-request', '15000',
        ], base_path());
        $ejecucion->setTimeout(120);
        $ejecucion->run();

        expect(is_file($archivoReporte), $ejecucion->getErrorOutput())->toBeTrue();
        $reporte = json_decode(File::get($archivoReporte), true, 512, JSON_THROW_ON_ERROR);
        $fallos = collect($reporte['run']['failures'])->map(fn (array $fallo): string => ($fallo['source']['name'] ?? 'Solicitud').': '.$fallo['error']['message'])->all();
        expect($fallos)->toBeEmpty();
        expect($ejecucion->getExitCode())->toBe(0);
        expect($reporte['run']['stats']['requests']['total'])->toBe(35);
        expect($reporte['run']['stats']['assertions']['failed'])->toBe(0);
        $this->assertDatabaseCount('establecimientos', 1);
        $this->assertDatabaseCount('analisis_laboratorio', 1);
        $this->assertDatabaseCount('permiso_vuelco', 1);
        expect(Establecimiento::sole()->user_id)->toBe($propietario->id);
        $analisis = AnalisisLaboratorio::sole();
        expect($analisis->resultado_final)->toBe('Aprobado');
        expect($analisis->revisado_por)->toBe($inspector->id);
        expect($analisis->parametros()->count())->toBeGreaterThan(0);
        expect(realpath($analisis->ruta_pdf))->toStartWith($directorio.DIRECTORY_SEPARATOR);
        $this->assertDatabaseHas('permiso_vuelco', ['numero_expediente' => 'DEMO-POSTMAN-001']);
    } finally {
        $servidor?->stop();
        DB::purge('entrega');
        $resuelto = realpath($directorio);
        if ($resuelto !== false && str_starts_with($resuelto, $raiz.DIRECTORY_SEPARATOR.'entrega-')) {
            File::deleteDirectory($resuelto);
        }
    }
});
