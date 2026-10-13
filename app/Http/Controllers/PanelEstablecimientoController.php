<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PanelEstablecimientoController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->es('inspector', 'admin_gobierno')) {
            return to_route('gestion');
        }

        Gate::authorize('create', Establecimiento::class);
        $datos = $request->validate(['establecimiento' => ['nullable', 'integer', 'min:1']]);
        $establecimientos = $request->user()->establecimientos()->orderBy('nombre')->get(['id', 'nombre']);
        $establecimiento = isset($datos['establecimiento'])
            ? $request->user()->establecimientos()->findOrFail($datos['establecimiento'])
            : $establecimientos->first();

        $analisis = null;
        $permiso = null;
        $estadoPermiso = null;
        $insignias = collect();
        $alertas = collect();
        if ($establecimiento !== null) {
            $analisis = $establecimiento->analisisLaboratorios()
                ->orderByDesc('fecha_muestra')->orderByDesc('analisis_laboratorio_id')->first();
            $permiso = $establecimiento->permisoVuelco()->first();
            $estadoPermiso = $permiso?->estado;
            if ($permiso?->estado === 'Activo') {
                $estadoPermiso = $permiso->fecha_vencimiento->lt(today()) ? 'Vencido'
                    : ($permiso->fecha_emision->gt(today()) ? 'Aún no vigente' : 'Activo');
            }
            $insignias = $establecimiento->insigniasCumplimiento()
                ->whereDate('fecha_otorgamiento', '<=', today())->orderByDesc('fecha_otorgamiento')->get();
            $alertas = $establecimiento->alertasClimaticas()
                ->where(function (Builder $query): void {
                    $query->whereDate('periodo_hasta', '>=', today())
                        ->orWhere(function (Builder $query): void {
                            $query->whereNull('periodo_hasta')->whereDate('fecha_evento', '>=', today());
                        });
                })->orderByDesc('consultado_en')->orderByDesc('alerta_climatica_id')->limit(5)->get();
        }

        [$evaluacion, $colorEvaluacion] = match (true) {
            $analisis === null => ['Sin análisis registrados', 'secondary'],
            $analisis->estado === 'observado' => ['Requiere revisión de la lectura', 'warning'],
            $analisis->estado === 'pendiente_lectura' => ['Lectura pendiente', 'secondary'],
            $analisis->resultado_sugerido === 'cumple' => ['Cumple', 'success'],
            $analisis->resultado_sugerido === 'alerta' => ['Alerta', 'warning'],
            $analisis->resultado_sugerido === 'incumple' => ['Incumple', 'danger'],
            default => ['Sin evaluación automática', 'secondary'],
        };

        return view('dashboard', compact('establecimientos', 'establecimiento', 'analisis', 'permiso',
            'estadoPermiso', 'insignias', 'alertas', 'evaluacion', 'colorEvaluacion'));
    }
}
