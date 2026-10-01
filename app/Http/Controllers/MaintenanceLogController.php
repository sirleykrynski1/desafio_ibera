<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\MaintenanceLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceLogController extends Controller
{
    /**
     * Listado de mantenimientos.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = MaintenanceLog::query()->with('establishment');

        if ($user && $user->role === 'propietario') {
            $query->whereHas('establishment', fn ($q) => $q->where('user_id', $user->id));
        }

        $logs = $query->latest('maintenance_date')->paginate(15);

        return view('maintenance_logs.index', compact('logs'));
    }

    /**
     * Muestra el formulario para registrar un mantenimiento.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $establishments = $user && $user->role === 'propietario'
            ? $user->establishments
            : Establishment::all();

        return view('maintenance_logs.create', compact('establishments'));
    }

    /**
     * Guarda el mantenimiento subiendo la imagen del ticket al disco público.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'establishment_id' => ['required', 'exists:establishments,id'],
            'maintenance_date' => ['required', 'date', 'before_or_equal:today'],
            'receipt_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Máximo 5MB
        ]);

        // Guardar la imagen en storage/app/public/receipts
        $imagePath = $request->file('receipt_image')->store('receipts', 'public');

        MaintenanceLog::create([
            'establishment_id' => $validated['establishment_id'],
            'maintenance_date' => $validated['maintenance_date'],
            'receipt_image' => $imagePath,
            'status' => 'pendiente', // Por defecto pendiente de validación por inspectores
        ]);

        return redirect()
            ->back()
            ->with('success', 'Registro de mantenimiento cargado con éxito. Queda pendiente de revisión.');
    }

    /**
     * Permite a inspectores o administradores cambiar el estado del mantenimiento.
     */
    public function updateStatus(Request $request, MaintenanceLog $maintenanceLog): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pendiente,aprobado,rechazado'],
        ]);

        $maintenanceLog->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->back()
            ->with('success', "El mantenimiento fue marcado como {$validated['status']}.");
    }
}
