<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarAnalisisRequest;
use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Services\RegistrarAnalisisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class AnalisisController extends Controller
{
    public function create(Request $request): View
    {
        Gate::authorize('create', Establecimiento::class);
        $establecimientos = $request->user()->establecimientos()->orderBy('nombre')->get();

        return view('cargar_analisis', compact('establecimientos'));
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Establecimiento::class);
        $analisis = AnalisisLaboratorio::query()->with('establecimiento')
            ->when($request->user()->rol === 'propietario', fn ($query) => $query->whereHas(
                'establecimiento', fn ($hoteles) => $hoteles->where('user_id', $request->user()->id),
            ))->orderByDesc('analisis_laboratorio_id')->paginate(15);

        return view('analisis_lista', compact('analisis'));
    }

    public function store(GuardarAnalisisRequest $request, RegistrarAnalisisService $servicio): RedirectResponse
    {
        $establecimiento = $request->establecimientoAutorizado();
        $ruta = $request->file('pdf')->store('analisis', 'local');
        abort_if($ruta === false, 503, 'No se pudo guardar el PDF. Intentá nuevamente.');
        try {
            $resultado = $servicio->registrar($establecimiento, Storage::disk('local')->path($ruta));
        } catch (Throwable $error) {
            Storage::disk('local')->delete($ruta);
            throw $error;
        }

        return to_route('analisis.mostrar', $resultado['analisis'])
            ->with('success', 'Análisis registrado. El dictamen final queda pendiente de revisión.')
            ->with('advertencias', $resultado['advertencias']);
    }

    public function show(AnalisisLaboratorio $analisis): View
    {
        Gate::authorize('view', $analisis->establecimiento);
        $analisis->load('parametros');

        return view('analisis_detalle', compact('analisis'));
    }
}
