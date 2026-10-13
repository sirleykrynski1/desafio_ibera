<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\InsigniaCumplimiento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InsigniaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('create', Establecimiento::class);
        $insignias = InsigniaCumplimiento::query()->with('establecimiento:id,nombre')
            ->whereHas('establecimiento', fn (Builder $query) => $query->where('user_id', $request->user()->id))
            ->whereDate('fecha_otorgamiento', '<=', today())
            ->orderByDesc('fecha_otorgamiento')->orderByDesc('insignia_cumplimiento_id')->paginate(12);

        return view('insignias', compact('insignias'));
    }
}
