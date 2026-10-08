<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirección para el Gobierno / Inspectores
    if ($rol === 'admin_gobierno' || $rol === 'inspector') {
        return redirect()->intended(route('analisis.cargar'));
    }

    // Redirección para los dueños de Establecimientos
    if ($rol === 'establecimiento') {
        // Reemplaza 'dashboard' por el nombre de la ruta principal del establecimiento si creaste una distinta
        return redirect()->intended(route('dashboard')); 
    }

    // Fallback por defecto por si un usuario no tiene rol asignado
    return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
