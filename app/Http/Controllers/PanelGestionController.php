<?php

namespace App\Http\Controllers;

use App\Models\AnalisisLaboratorio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelGestionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array($request->user()->rol, ['inspector', 'admin_gobierno'], true), 403);
        $analisis = AnalisisLaboratorio::with('establecimiento')
            ->where('resultado_final', 'Pendiente')->orderBy('created_at')->paginate(15);

        return view('gestion', compact('analisis'));
    }

    public function show(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->rol, ['propietario', 'inspector', 'admin_gobierno'], true), 403);

        return to_route($request->user()->rol === 'propietario' ? 'establecimientos.indice' : 'gestion');
    }
}
