<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\OccupancyLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OccupancyLogController extends Controller
{
    /**
     * Listado de registros de ocupación.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = OccupancyLog::query()->with('establishment');

        if ($user && $user->role === 'propietario') {
            $query->whereHas('establishment', fn ($q) => $q->where('user_id', $user->id));
        }

        $occupancyLogs = $query->latest('date_reported')->paginate(15);

        return view('occupancy_logs.index', compact('occupancyLogs'));
    }

    /**
     * Muestra el formulario para cargar ocupación.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $establishments = $user && $user->role === 'propietario'
            ? $user->establishments
            : Establishment::all();

        return view('occupancy_logs.create', compact('establishments'));
    }

    /**
     * Guarda el registro de ocupación validando que no supere la capacidad máxima.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'establishment_id' => ['required', 'exists:establishments,id'],
            'current_guests' => ['required', 'integer', 'min:0'],
            'date_reported' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $establishment = Establishment::findOrFail($validated['establishment_id']);

        // Validación de negocio: la cantidad de huéspedes no puede superar la capacidad máxima
        if ($validated['current_guests'] > $establishment->max_capacity) {
            throw ValidationException::withMessages([
                'current_guests' => "La cantidad de huéspedes ({$validated['current_guests']}) supera la capacidad máxima permitida de este establecimiento ({$establishment->max_capacity} personas).",
            ]);
        }

        OccupancyLog::create($validated);

        return redirect()
            ->back()
            ->with('success', 'Registro de ocupación guardado correctamente.');
    }
}
