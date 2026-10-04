<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MantenimientoController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS DE PROTOTIPADO Y VISUALIZACIÓN DIRECTA (Sin Middleware)
|--------------------------------------------------------------------------
| Permite visualizar las vistas directamente desde cualquier dominio o puerto:
| - http://desafio_ibera.test
| - http://localhost:8000
| - http://127.0.0.1:8000
*/

// Página principal -> Redirige al Dashboard
Route::get('/', function () {
    return view('dashboard.index');
})->name('inicio');

// 🐊 Dashboard Gobierno / Ambiental
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('tablero');

// 🦦 Mis Establecimientos
Route::get('/establishments', function () {
    return view('establishments.index');
})->name('establecimientos.indice');

// 🐸 Registrar Mantenimiento / Limpieza
Route::get('/maintenance/create', function () {
    return view('maintenance.create');
})->name('mantenimiento.crear');
// 🐸 Recibir y guardar los datos del formulario (POST)
Route::post('/maintenance', [MantenimientoController::class, 'store'])->name('mantenimiento.guardar');