<?php

namespace App\Http\Controllers;

use App\Models\AnalisisLaboratorio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RevisionAnalisisController extends Controller
{
    public function store(Request $request, AnalisisLaboratorio $analisis): RedirectResponse
    {
        Gate::authorize('revisarAnalisis', $analisis->establecimiento);
        $datos = $request->validate([
            'resultado_final' => ['required', 'in:Aprobado,Rechazado'],
            'observaciones_revision' => ['required', 'string', 'max:2000'],
        ]);

        $actualizados = AnalisisLaboratorio::whereKey($analisis->getKey())
            ->where('resultado_final', 'Pendiente')
            ->whereIn('estado', ['evaluado', 'observado'])
            ->update([
                'resultado_final' => $datos['resultado_final'],
                'observaciones_revision' => $datos['observaciones_revision'],
                'revisado_por' => $request->user()->id,
                'revisado_en' => now(),
                'estado' => 'revisado',
            ]);

        if ($actualizados === 0) {
            throw ValidationException::withMessages([
                'resultado_final' => 'El análisis ya fue revisado o todavía no terminó de procesarse. Actualizá la página.',
            ]);
        }

        return to_route('analisis.mostrar', $analisis)->with('success', 'Dictamen registrado.');
    }
}
