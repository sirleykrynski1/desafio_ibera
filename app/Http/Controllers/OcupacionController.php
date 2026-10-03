<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Ocupacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OcupacionController extends Controller
{
    /**
     * Listado de registros de ocupación.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Ocupacion::query()->with('establecimiento');

        if ($user && $user->rol === 'propietario') {
            $query->whereHas('establecimiento', fn ($q) => $q->where('user_id', $user->id));
        }

        $ocupaciones = $query->latest('fecha_registro')->paginate(15);

        return view('occupancy.index', compact('ocupaciones'));
    }

    /**
     * Muestra el formulario para cargar ocupación.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $establecimientos = $user && $user->rol === 'propietario'
            ? $user->establecimientos
            : Establecimiento::all();

        return view('occupancy.create', compact('establecimientos'));
    }

    /**
     * Guarda el registro de ocupación validando que no supere la capacidad máxima.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'establecimiento_id' => ['required', 'exists:establecimientos,id'],
            'numero_personas' => ['required', 'integer', 'min:0'],
            'fecha_registro' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $establecimiento = Establecimiento::findOrFail($validated['establecimiento_id']);

        // Validación de negocio: la cantidad de personas no puede superar la capacidad máxima
        if ($validated['numero_personas'] > $establecimiento->capacidad_maxima) {
            throw ValidationException::withMessages([
                'numero_personas' => "La cantidad de personas ({$validated['numero_personas']}) supera la capacidad máxima permitida de este establecimiento ({$establecimiento->capacidad_maxima} personas).",
            ]);
        }

        Ocupacion::create($validated);

        return redirect()
            ->back()
            ->with('success', 'Registro de ocupación guardado correctamente.');
    }
}
