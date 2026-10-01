<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablishmentController extends Controller
{
    /**
     * Muestra el listado de establecimientos.
     * Si es propietario, ve solo los suyos; si es gobierno/inspector, ve todos.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Establishment::query()->with(['user', 'latestRiskEvaluation']);

        if ($user && $user->role === 'propietario') {
            $query->where('user_id', $user->id);
        }

        $establishments = $query->latest()->paginate(15);

        return view('establishments.index', compact('establishments'));
    }

    /**
     * Muestra el formulario para crear un nuevo establecimiento.
     */
    public function create(): View
    {
        return view('establishments.create');
    }

    /**
     * Almacena un nuevo establecimiento asociándolo al usuario autenticado.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:hotel,gastronomico,comercio'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'biodigester_capacity_l' => ['required', 'integer', 'min:1'],
        ]);

        // Asignación automática del user_id del usuario autenticado
        $establishment = $request->user()->establishments()->create($validated);

        return redirect()
            ->route('establishments.show', $establishment)
            ->with('success', 'Establecimiento registrado exitosamente.');
    }

    /**
     * Muestra el detalle de un establecimiento específico.
     */
    public function show(Establishment $establishment): View
    {
        $establishment->load([
            'user',
            'latestRiskEvaluation',
            'occupancyLogs' => fn ($q) => $q->latest('date_reported')->limit(10),
            'maintenanceLogs' => fn ($q) => $q->latest('maintenance_date')->limit(10),
            'ecoBadges',
        ]);

        return view('establishments.show', compact('establishment'));
    }

    /**
     * Muestra el formulario para editar el establecimiento.
     */
    public function edit(Establishment $establishment): View
    {
        return view('establishments.edit', compact('establishment'));
    }

    /**
     * Actualiza el establecimiento en la base de datos.
     */
    public function update(Request $request, Establishment $establishment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:hotel,gastronomico,comercio'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'biodigester_capacity_l' => ['required', 'integer', 'min:1'],
        ]);

        $establishment->update($validated);

        return redirect()
            ->route('establishments.show', $establishment)
            ->with('success', 'Establecimiento actualizado correctamente.');
    }

    /**
     * Elimina el establecimiento.
     */
    public function destroy(Establishment $establishment): RedirectResponse
    {
        $establishment->delete();

        return redirect()
            ->route('establishments.index')
            ->with('success', 'Establecimiento eliminado correctamente.');
    }
}
