<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PermisoVuelcoController extends Controller
{
    public function store(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        Gate::authorize('gestionarPermiso', $establecimiento);
        $datos = $request->validate([
            'numero_expediente' => ['required', 'string', 'max:255'],
            'fecha_emision' => ['required', 'date_format:Y-m-d'],
            'fecha_vencimiento' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_emision'],
            'estado' => ['required', 'in:Activo,Vencido,Revocado'],
            'estado_tramite' => ['required', 'in:iniciado,pendiente_documentacion,en_evaluacion,resuelto'],
            'tipo_destino_vuelco' => ['required', 'in:cursos_agua,laguna,conducto_pluvial,absorcion_suelo'],
        ]);
        DB::transaction(function () use ($establecimiento, $datos): void {
            $bloqueado = Establecimiento::whereKey($establecimiento->id)->lockForUpdate()->firstOrFail();
            $bloqueado->permisoVuelco()->updateOrCreate([], $datos);
        });

        return to_route('establecimientos.mostrar', $establecimiento)->with('success', 'Permiso actualizado.');
    }
}
