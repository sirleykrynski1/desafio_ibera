<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Mantenimiento;
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
        $esGobiernoOInspector = $user && in_array($user->rol, ['admin_gobierno', 'inspector']);

        // 1. Filtrar establecimientos según el rol
        $query = Establecimiento::query()
            ->with([
                'ultimoRiesgo',
                'ultimaOcupacion',
                'ultimoMantenimiento',
                'user:id,nombre,apellido,telefono',
            ]);

        if (! $esGobiernoOInspector && $user) {
            // El propietario solo ve sus propios establecimientos
            $query->where('user_id', $user->id);
        }

        $establecimientos = $query->get();

        // 2. Formatear datos para los marcadores del mapa en el frontend
        $marcadores = $establecimientos->map(function (Establecimiento $establecimiento) use ($esGobiernoOInspector) {
            $ultimoRiesgo = $establecimiento->ultimoRiesgo;

            $datosMarcador = [
                'id' => $establecimiento->id,
                'nombre' => $establecimiento->nombre,
                'rubro' => $establecimiento->rubro,
                'latitud' => (float) $establecimiento->latitud,
                'longitud' => (float) $establecimiento->longitud,
                'capacidad_maxima' => $establecimiento->capacidad_maxima,
                'capacidad_biodigestor' => $establecimiento->capacidad_biodigestor,
                'capacidad_fosa_litros' => $establecimiento->capacidad_fosa_litros,
                'ocupacion_actual' => $establecimiento->ocupacion_actual,
                'fecha_ultimo_desagote' => $establecimiento->fecha_ultimo_desagote,
                'nivel_riesgo' => $ultimoRiesgo?->nivel_riesgo ?? 'sin_evaluar',
                'lluvia_pronosticada' => $ultimoRiesgo?->lluvia_pronosticada ?? 0,
                'fecha_evaluacion' => $ultimoRiesgo?->fecha_evaluacion?->toIso8601String(),
            ];

            // Si es gobierno o inspector, incluimos datos de contacto del dueño
            if ($esGobiernoOInspector) {
                $datosMarcador['nombre_propietario'] = $establecimiento->user ? "{$establecimiento->user->nombre} {$establecimiento->user->apellido}" : 'N/A';
                $datosMarcador['telefono_propietario'] = $establecimiento->user?->telefono ?? 'N/A';
            }

            return $datosMarcador;
        });

        // 3. Métricas y KPIs adaptados al rol
        $estadisticas = [
            'total_establecimientos' => $establecimientos->count(),
            'conteo_riesgos' => [
                'verde' => $establecimientos->filter(fn ($e) => $e->ultimoRiesgo?->nivel_riesgo === 'verde')->count(),
                'amarillo' => $establecimientos->filter(fn ($e) => $e->ultimoRiesgo?->nivel_riesgo === 'amarillo')->count(),
                'rojo' => $establecimientos->filter(fn ($e) => $e->ultimoRiesgo?->nivel_riesgo === 'rojo')->count(),
                'sin_evaluar' => $establecimientos->filter(fn ($e) => ! $e->ultimoRiesgo)->count(),
            ],
        ];

        if ($esGobiernoOInspector) {
            $estadisticas['mantenimientos_pendientes'] = Mantenimiento::where('estado', 'pendiente')->count();
        } else {
            $estadisticas['mis_emblemas_ecologicos'] = $user ? $user->establecimientos()->withCount('emblemasEcologicos')->get()->sum('emblemas_ecologicos_count') : 0;
            $estadisticas['mis_mantenimientos_pendientes'] = $user ? Mantenimiento::whereHas('establecimiento', fn ($q) => $q->where('user_id', $user->id))->where('estado', 'pendiente')->count() : 0;
        }

        // Si la petición espera JSON (ej. fetch desde frontend o API)
        if ($request->wantsJson()) {
            return response()->json([
                'rol_usuario' => $user?->rol ?? 'invitado',
                'estadisticas' => $estadisticas,
                'marcadores_mapa' => $marcadores,
            ]);
        }

        // Si es petición web tradicional, retorna la vista con los datos compactados
        return view('dashboard.index', compact('estadisticas', 'marcadores', 'establecimientos', 'esGobiernoOInspector'));
    }
}
