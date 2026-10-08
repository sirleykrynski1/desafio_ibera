<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ClimateApiClient
{
    /**
     * @return array{version: string, estado: string, ubicacion: array{latitud: float, longitud: float}, fuente: string, consultado_en: string, periodo: array{desde: string, hasta: string, zona_horaria: string}, precipitacion_acumulada_mm: float, alerta: array{activa: bool, codigo: ?string, motivos: list<string>}, version_reglas: string}|null
     */
    public function fetchAlert(float $latitude, float $longitude): ?array
    {
        if (! is_finite($latitude) || ! is_finite($longitude) || abs($latitude) > 90 || abs($longitude) > 180) {
            throw new InvalidArgumentException('Las coordenadas están fuera del rango permitido.');
        }

        try {
            $response = Http::baseUrl(rtrim(config('services.climate.url'), '/'))
                ->acceptJson()
                ->connectTimeout(config('services.climate.connect_timeout'))
                ->timeout(config('services.climate.timeout'))
                ->withoutRedirecting()
                ->get('/api/v1/alerta-climatica', [
                    'latitud' => $latitude,
                    'longitud' => $longitude,
                ]);
        } catch (ConnectionException) {
            Log::warning('No se pudo conectar con la API climática.');

            return null;
        }

        if (! $response->ok()) {
            Log::warning('La API climática no devolvió un pronóstico disponible.', ['status' => $response->status()]);

            return null;
        }

        $payload = $response->json();
        $validator = Validator::make(is_array($payload) ? $payload : [], [
            'version' => ['required', 'string', 'in:1'],
            'estado' => ['required', 'in:disponible'],
            'ubicacion.latitud' => ['required', 'numeric', 'between:-90,90'],
            'ubicacion.longitud' => ['required', 'numeric', 'between:-180,180'],
            'fuente' => ['required', 'string'],
            'consultado_en' => ['required', 'date'],
            'periodo.desde' => ['required', 'date_format:Y-m-d'],
            'periodo.hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:periodo.desde'],
            'periodo.zona_horaria' => ['required', 'string'],
            'precipitacion_acumulada_mm' => ['required', 'numeric', 'min:0'],
            'alerta.activa' => ['required', 'boolean:strict'],
            'alerta.codigo' => ['present', 'nullable', 'in:PRECIPITACION_ACUMULADA_ALTA'],
            'alerta.motivos' => ['present', 'array'],
            'alerta.motivos.*' => ['string'],
            'version_reglas' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            Log::warning('La API climática devolvió una respuesta inválida.');

            return null;
        }

        $data = $validator->validated();
        if ((float) $data['ubicacion']['latitud'] !== $latitude
            || (float) $data['ubicacion']['longitud'] !== $longitude
            || ($data['alerta']['activa'] && ($data['alerta']['codigo'] === null || $data['alerta']['motivos'] === []))
            || (! $data['alerta']['activa'] && $data['alerta']['codigo'] !== null)) {
            Log::warning('La API climática devolvió una respuesta inconsistente.');

            return null;
        }

        return $data;
    }
}
