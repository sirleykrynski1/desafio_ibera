<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Riesgo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RiesgoController extends Controller
{
    /**
     * Endpoint API para el motor de Python.
     * Recibe los datos procesados de riesgo ambiental e impacto de lluvias.
     */
    public function guardarRiesgoDesdePython(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'establecimiento_id' => ['required', 'integer', 'exists:establecimientos,id'],
            'nivel_riesgo' => ['required', 'in:verde,amarillo,rojo'],
            'lluvia_pronosticada' => ['required', 'numeric', 'min:0'],
            'fecha_evaluacion' => ['nullable', 'date'],
        ]);

        $evaluacion = Riesgo::create([
            'establecimiento_id' => $validated['establecimiento_id'],
            'nivel_riesgo' => $validated['nivel_riesgo'],
            'lluvia_pronosticada' => $validated['lluvia_pronosticada'],
            'fecha_evaluacion' => $validated['fecha_evaluacion'] ?? now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Evaluación de riesgo registrada exitosamente desde el motor Python.',
            'data' => $evaluacion,
        ], Response::HTTP_CREATED);
    }
}
