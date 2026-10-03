<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Mantenimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MantenimientoController extends Controller
{
    /**
     * Listado de mantenimientos.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Mantenimiento::query()->with('establecimiento');

        if ($user && $user->rol === 'propietario') {
            $query->whereHas('establecimiento', fn ($q) => $q->where('user_id', $user->id));
        }

        $mantenimientos = $query->latest('fecha_mantenimiento')->paginate(15);

        return view('maintenance.index', compact('mantenimientos'));
    }

    /**
     * Muestra el formulario para registrar un mantenimiento.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $establecimientos = $user && $user->rol === 'propietario'
            ? $user->establecimientos
            : Establecimiento::all();

        return view('maintenance.create', compact('establecimientos'));
    }

    /**
     * Guarda el mantenimiento subiendo la imagen del ticket al disco público.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'establecimiento_id' => ['required', 'exists:establecimientos,id'],
            'fecha_mantenimiento' => ['required', 'date', 'before_or_equal:today'],
            'comprobante_foto' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Máximo 5MB
        ]);

        // Guardar la imagen en storage/app/public/receipts
        $imagePath = $request->file('comprobante_foto')->store('receipts', 'public');

        Mantenimiento::create([
            'establecimiento_id' => $validated['establecimiento_id'],
            'fecha_mantenimiento' => $validated['fecha_mantenimiento'],
            'comprobante_foto' => $imagePath,
            'estado' => 'pendiente', // Por defecto pendiente de validación por inspectores
        ]);

        return redirect()
            ->back()
            ->with('success', 'Registro de mantenimiento cargado con éxito. Queda pendiente de revisión.');
    }

    /**
     * Permite a inspectores o administradores cambiar el estado del mantenimiento.
     */
    public function updateStatus(Request $request, Mantenimiento $mantenimiento): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:pendiente,aprobado,rechazado'],
        ]);

        $mantenimiento->update([
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->back()
            ->with('success', "El mantenimiento fue marcado como {$validated['estado']}.");
    }
}
