<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskEvaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RiskEvaluationController extends Controller
{
    /**
     * Endpoint API para el motor de Python.
     * Recibe los datos procesados de riesgo ambiental e impacto de lluvias.
     */
    public function storeRiskFromPython(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'establishment_id' => ['required', 'integer', 'exists:establishments,id'],
            'risk_level' => ['required', 'in:verde,amarillo,rojo'],
            'rain_forecast_mm' => ['required', 'numeric', 'min:0'],
            'evaluation_date' => ['nullable', 'date'],
        ]);

        $evaluation = RiskEvaluation::create([
            'establishment_id' => $validated['establishment_id'],
            'risk_level' => $validated['risk_level'],
            'rain_forecast_mm' => $validated['rain_forecast_mm'],
            'evaluation_date' => $validated['evaluation_date'] ?? now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Evaluación de riesgo registrada exitosamente desde el motor Python.',
            'data' => $evaluation,
        ], Response::HTTP_CREATED);
    }
}
