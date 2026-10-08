<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Services\ClimateApiClient;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClimaEstablecimientoController extends Controller
{
    public function show(Establecimiento $establecimiento, ClimateApiClient $cliente): View
    {
        Gate::authorize('view', $establecimiento);
        $coordenadasValidas = $establecimiento->latitud !== null && $establecimiento->longitud !== null
            && is_numeric($establecimiento->latitud) && is_numeric($establecimiento->longitud)
            && is_finite((float) $establecimiento->latitud) && is_finite((float) $establecimiento->longitud)
            && abs((float) $establecimiento->latitud) <= 90 && abs((float) $establecimiento->longitud) <= 180;
        $alerta = $coordenadasValidas
            ? $cliente->fetchAlert((float) $establecimiento->latitud, (float) $establecimiento->longitud)
            : null;

        return view('clima_establecimiento', compact('establecimiento', 'alerta', 'coordenadasValidas'));
    }
}
