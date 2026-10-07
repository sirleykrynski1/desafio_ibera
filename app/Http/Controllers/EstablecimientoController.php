<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarEstablecimientoRequest;
use App\Models\Establecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EstablecimientoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Establecimiento::class);
        $establecimientos = Establecimiento::query()
            ->when($request->user()->rol === 'propietario', fn ($query) => $query->where('user_id', $request->user()->id))
            ->orderByDesc('id')->paginate(15);

        return view('establishments.index', compact('establecimientos'));
    }

    public function create(): View
    {
        Gate::authorize('create', Establecimiento::class);

        return view('establecimiento_form', ['establecimiento' => new Establecimiento]);
    }

    public function store(GuardarEstablecimientoRequest $request): RedirectResponse
    {
        $establecimiento = $request->user()->establecimientos()->create($request->validated());

        return to_route('establecimientos.mostrar', $establecimiento)->with('success', 'Establecimiento registrado.');
    }

    public function show(Establecimiento $establecimiento): View
    {
        Gate::authorize('view', $establecimiento);
        $establecimiento->load('permisoVuelco');

        return view('establecimiento_detalle', compact('establecimiento'));
    }

    public function edit(Establecimiento $establecimiento): View
    {
        Gate::authorize('update', $establecimiento);

        return view('establecimiento_form', compact('establecimiento'));
    }

    public function update(GuardarEstablecimientoRequest $request, Establecimiento $establecimiento): RedirectResponse
    {
        $establecimiento->update($request->validated());

        return to_route('establecimientos.mostrar', $establecimiento)->with('success', 'Establecimiento actualizado.');
    }
}
