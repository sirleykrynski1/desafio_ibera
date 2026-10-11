<?php

use App\Jobs\ActualizarAlertaClimatica;
use App\Models\Establecimiento;
use App\Services\ClimateApiClient;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('clima:consultar {latitud} {longitud}', function (ClimateApiClient $climate): int {
    $validator = Validator::make($this->arguments(), [
        'latitud' => ['required', 'numeric', 'between:-90,90'],
        'longitud' => ['required', 'numeric', 'between:-180,180'],
    ]);

    if ($validator->fails()) {
        $this->error('Ingresá latitud entre -90 y 90 y longitud entre -180 y 180.');

        return 1;
    }

    $coordinates = $validator->validated();
    $alert = $climate->fetchAlert((float) $coordinates['latitud'], (float) $coordinates['longitud']);
    if ($alert === null) {
        $this->error('No se pudo obtener una alerta climática válida. Revisá FastAPI y CLIMATE_API_URL.');

        return 1;
    }

    $this->line(json_encode($alert, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

    return 0;
})->purpose('Consultar la API climática sin guardar datos');

Artisan::command('clima:actualizar', function (): int {
    $fallos = 0;
    $consultados = 0;
    $ciclo = (string) intdiv(now()->timestamp, 6 * 60 * 60);
    foreach (Establecimiento::query()->select('id')->lazyById(100) as $hotel) {
        $consultados++;
        try {
            if (! ActualizarAlertaClimatica::dispatchSync($hotel->id, $ciclo)) {
                $fallos++;
            }
        } catch (Throwable $error) {
            report($error);
            $fallos++;
        }
    }
    $this->info("Establecimientos procesados: {$consultados}. Consultas fallidas: {$fallos}.");

    return $fallos > 0 ? 1 : 0;
})->purpose('Consultar Python y guardar alertas sin duplicar el ciclo de seis horas');

Schedule::command('clima:actualizar')->everySixHours()->timezone('UTC')->withoutOverlapping();
