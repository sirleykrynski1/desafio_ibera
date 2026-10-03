<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablecimientoController extends Controller
{
    /**
     * Muestra el listado de establecimientos.
     * Si es propietario, ve solo los suyos; si es gobierno/inspector, ve todos.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Establecimiento::query()->with(['user', 'ultimoRiesgo']);

        if ($user && $user->rol === 'propietario') {
            $query->where('user_id', $user->id);
        }

        $establecimientos = $query->latest()->paginate(15);

        return view('establishments.index', compact('establecimientos'));
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
            'nombre' => ['required', 'string', 'max:255'],
            'rubro' => ['required', 'in:hotel,gastronomico,comercio'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'capacidad_maxima' => ['required', 'integer', 'min:1'],
            'capacidad_biodigestor' => ['required', 'integer', 'min:1'],
        ]);

        // Asignación automática del user_id del usuario autenticado
        $establecimiento = $request->user()->establecimientos()->create($validated);

        return redirect()
            ->route('establecimientos.mostrar', $establecimiento)
            ->with('success', 'Establecimiento registrado exitosamente.');
    }

    /**
     * Muestra el detalle de un establecimiento específico.
     */
    public function show(Establecimiento $establecimiento): View
    {
        $establecimiento->load([
            'user',
            'ultimoRiesgo',
            'ocupaciones' => fn ($q) => $q->latest('fecha_registro')->limit(10),
            'mantenimientos' => fn ($q) => $q->latest('fecha_mantenimiento')->limit(10),
            'emblemasEcologicos',
        ]);

        return view('establishments.show', compact('establecimiento'));
    }

    /**
     * Muestra el formulario para editar el establecimiento.
     */
    public function edit(Establecimiento $establecimiento): View
    {
        return view('establishments.edit', compact('establecimiento'));
    }

    /**
     * Actualiza el establecimiento en la base de datos.
     */
    public function update(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'rubro' => ['required', 'in:hotel,gastronomico,comercio'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'capacidad_maxima' => ['required', 'integer', 'min:1'],
            'capacidad_biodigestor' => ['required', 'integer', 'min:1'],
        ]);

        $establecimiento->update($validated);

        return redirect()
            ->route('establecimientos.mostrar', $establecimiento)
            ->with('success', 'Establecimiento actualizado correctamente.');
    }

    /**
     * Elimina el establecimiento.
     */
    public function destroy(Establecimiento $establecimiento): RedirectResponse
    {
        $establecimiento->delete();

        return redirect()
            ->route('establecimientos.indice')
            ->with('success', 'Establecimiento eliminado correctamente.');
    }
}
