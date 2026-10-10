<?php

namespace App\Http\Controllers;

use App\Models\AlertaClimatica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertaClimaticaController extends Controller
{
    public function index(Request $request): View
    {
        $this->autorizar($request);
        $datos = $request->validate(['estado' => ['nullable', 'in:pendientes,revisadas,todas']]);
        $estado = $datos['estado'] ?? 'pendientes';
        $alertas = AlertaClimatica::with('establecimiento')
            ->when($estado === 'pendientes', fn ($query) => $query->whereNull('revisada_en'))
            ->when($estado === 'revisadas', fn ($query) => $query->whereNotNull('revisada_en'))
            ->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('alertas_climaticas', compact('alertas', 'estado'));
    }

    public function update(Request $request, AlertaClimatica $alerta): RedirectResponse
    {
        $this->autorizar($request);
        AlertaClimatica::whereKey($alerta->getKey())->whereNull('revisada_en')
            ->update(['revisada_en' => now(), 'revisada_por' => $request->user()->id]);

        return to_route('alertas.indice')->with('success', 'Alerta marcada como revisada.');
    }

    private function autorizar(Request $request): void
    {
        abort_unless(in_array($request->user()->rol, ['inspector', 'admin_gobierno'], true), 403);
    }
}
