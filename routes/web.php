<?php

use App\Http\Controllers\AlertaClimaticaController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\ClimaEstablecimientoController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\PanelGestionController;
use App\Http\Controllers\PermisoVuelcoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevisionAnalisisController;
use App\Http\Controllers\SesionController;
use Illuminate\Support\Facades\Route;

// Redirige la página principal directo al dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard del hotel
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Formulario de carga de análisis (Subir PDF / Carga Manual)
Route::get('/analisis', function () {
    return view('cargar_analisis');
})->name('analisis.create');

// Historial de análisis
Route::get('/historial', function () {
    return view('historial');
})->name('analisis.index');

// Panel de insignias de cumplimiento
Route::get('/insignias', function () {
    return view('insignias');
})->name('insignias.index');

// Ruta de cierre de sesión (Logout)
Route::post('/logout', function () {
    // Aquí la Persona 2 del backend luego agregará la lógica de cierre de sesión,
    // por ahora redirige al inicio o login.
    return redirect()->route('dashboard');
})->name('logout');
