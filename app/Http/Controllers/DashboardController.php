<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\MaintenanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Panel principal y mapa interactivo de monitoreo de riesgos.
     * Adapta los datos según el rol del usuario autenticado.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $isGobiernoOrInspector = $user && in_array($user->role, ['admin_gobierno', 'inspector']);

        // 1. Filtrar establecimientos según el rol
        $query = Establishment::query()
            ->with([
                'latestRiskEvaluation',
                'latestOccupancyLog',
                'latestMaintenanceLog',
                'user:id,name,apellido,telefono',
            ]);

        if (! $isGobiernoOrInspector && $user) {
            // El propietario solo ve sus propios establecimientos
            $query->where('user_id', $user->id);
        }

        $establishments = $query->get();

        // 2. Formatear datos para los marcadores de Leaflet en el Frontend
        $mapMarkers = $establishments->map(function (Establishment $establishment) use ($isGobiernoOrInspector) {
            $latestRisk = $establishment->latestRiskEvaluation;

            $markerData = [
                'id' => $establishment->id,
                'name' => $establishment->name,
                'type' => $establishment->type,
                'latitude' => (float) $establishment->latitude,
                'longitude' => (float) $establishment->longitude,
                'max_capacity' => $establishment->max_capacity,
                'biodigester_capacity_l' => $establishment->biodigester_capacity_l,
                'capacidad_fosa_litros' => $establishment->capacidad_fosa_litros,
                'ocupacion_actual' => $establishment->ocupacion_actual,
                'fecha_ultimo_desagote' => $establishment->fecha_ultimo_desagote,
                'risk_level' => $latestRisk?->risk_level ?? 'sin_evaluar',
                'rain_forecast_mm' => $latestRisk?->rain_forecast_mm ?? 0,
                'last_evaluated_at' => $latestRisk?->evaluation_date?->toIso8601String(),
            ];

            // Si es gobierno o inspector, incluimos datos de contacto del dueño
            if ($isGobiernoOrInspector) {
                $markerData['owner_name'] = $establishment->user ? "{$establishment->user->name} {$establishment->user->apellido}" : 'N/A';
                $markerData['owner_phone'] = $establishment->user?->telefono ?? 'N/A';
            }

            return $markerData;
        });

        // 3. Métricas y KPIs adaptados al rol
        $stats = [
            'total_establishments' => $establishments->count(),
            'risk_counts' => [
                'verde' => $establishments->filter(fn ($e) => $e->latestRiskEvaluation?->risk_level === 'verde')->count(),
                'amarillo' => $establishments->filter(fn ($e) => $e->latestRiskEvaluation?->risk_level === 'amarillo')->count(),
                'rojo' => $establishments->filter(fn ($e) => $e->latestRiskEvaluation?->risk_level === 'rojo')->count(),
                'sin_evaluar' => $establishments->filter(fn ($e) => ! $e->latestRiskEvaluation)->count(),
            ],
        ];

        if ($isGobiernoOrInspector) {
            $stats['pending_maintenances'] = MaintenanceLog::where('status', 'pendiente')->count();
        } else {
            $stats['my_eco_badges_count'] = $user ? $user->establishments()->withCount('ecoBadges')->get()->sum('eco_badges_count') : 0;
            $stats['my_pending_maintenances'] = $user ? MaintenanceLog::whereHas('establishment', fn ($q) => $q->where('user_id', $user->id))->where('status', 'pendiente')->count() : 0;
        }

        // Si la petición espera JSON (ej. fetch desde frontend o API)
        if ($request->wantsJson()) {
            return response()->json([
                'user_role' => $user?->role ?? 'guest',
                'stats' => $stats,
                'map_markers' => $mapMarkers,
            ]);
        }

        // Si es petición web tradicional, retorna la vista con los datos compactados
        return view('dashboard', compact('stats', 'mapMarkers', 'establishments', 'isGobiernoOrInspector'));
    }
}
