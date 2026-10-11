<?php

namespace App\Jobs;

use App\Models\AlertaClimatica;
use App\Models\Establecimiento;
use App\Services\ClimateApiClient;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ActualizarAlertaClimatica
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $establecimientoId, public string $ciclo) {}

    /**
     * Execute the job.
     */
    public function handle(ClimateApiClient $cliente): bool
    {
        $hotel = Establecimiento::find($this->establecimientoId);
        if ($hotel === null) {
            return true;
        }
        if ($hotel->latitud === null || $hotel->longitud === null
            || ! is_numeric($hotel->latitud) || ! is_numeric($hotel->longitud)
            || ! is_finite((float) $hotel->latitud) || ! is_finite((float) $hotel->longitud)
            || abs((float) $hotel->latitud) > 90 || abs((float) $hotel->longitud) > 180) {
            Log::warning('No se consultó clima: coordenadas inválidas.', ['establecimiento_id' => $hotel->id]);

            return false;
        }
        $pronostico = $cliente->fetchAlert((float) $hotel->latitud, (float) $hotel->longitud);
        if ($pronostico === null) {
            return false;
        }
        $consulta = Carbon::parse($pronostico['consultado_en'])->utc();
        if ($consulta->lt(now()->utc()->subHours(6)) || $consulta->gt(now()->utc()->addMinutes(5))
            || $pronostico['precipitacion_acumulada_mm'] > 999999.99) {
            Log::warning('Pronóstico fuera de vigencia o rango.', ['establecimiento_id' => $hotel->id]);

            return false;
        }
        if (! $pronostico['alerta']['activa']) {
            return true;
        }

        AlertaClimatica::firstOrCreate([
            'clave_consulta' => hash('sha256', $hotel->id.'|'.$this->ciclo),
        ], [
            'establecimiento_id' => $hotel->id,
            'fecha_evento' => $pronostico['periodo']['desde'],
            'periodo_hasta' => $pronostico['periodo']['hasta'],
            'tipo' => $pronostico['alerta']['codigo'],
            'milimetros_lluvia' => $pronostico['precipitacion_acumulada_mm'],
            'consultado_en' => $consulta,
            'detalle' => $pronostico,
        ]);

        return true;
    }
}
