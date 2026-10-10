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



Route::view('/', 'welcome')->name('inicio');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SesionController::class, 'create'])->name('login');
    Route::post('/login', [SesionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::post('/registro', [SesionController::class, 'register'])->middleware('throttle:6,1')->name('registro');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/alertas-climaticas', [AlertaClimaticaController::class, 'index'])->name('alertas.indice');
    Route::patch('/alertas-climaticas/{alerta}', [AlertaClimaticaController::class, 'update'])->name('alertas.revisar');
    Route::post('/logout', [SesionController::class, 'destroy'])->name('logout');

    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/gestion', [PanelGestionController::class, 'index'])->name('gestion');

    Route::get('/establecimientos/{establecimiento}/clima', [ClimaEstablecimientoController::class, 'show'])->middleware('throttle:20,1')->name('establecimientos.clima');
    Route::get('/establecimientos', [EstablecimientoController::class, 'index'])->name('establecimientos.indice');
    Route::get('/establecimientos/crear', [EstablecimientoController::class, 'create'])->name('establecimientos.crear');
    Route::post('/establecimientos', [EstablecimientoController::class, 'store'])->name('establecimientos.guardar');
    Route::get('/establecimientos/{establecimiento}', [EstablecimientoController::class, 'show'])->name('establecimientos.mostrar');
    Route::get('/establecimientos/{establecimiento}/editar', [EstablecimientoController::class, 'edit'])->name('establecimientos.editar');
    Route::put('/establecimientos/{establecimiento}', [EstablecimientoController::class, 'update'])->name('establecimientos.actualizar');
    Route::post('/establecimientos/{establecimiento}/permiso', [PermisoVuelcoController::class, 'store'])->name('permisos.guardar');

    Route::get('/analisis', [AnalisisController::class, 'create'])->name('analisis.crear');
    Route::post('/analisis', [AnalisisController::class, 'store'])->middleware('throttle:10,1')->name('analisis.guardar');
    Route::post('/analisis/{analisis}/revision', [RevisionAnalisisController::class, 'store'])->name('analisis.revision');
    Route::get('/historial', [AnalisisController::class, 'index'])->name('analisis.indice');
    Route::get('/analisis/{analisis}', [AnalisisController::class, 'show'])->name('analisis.mostrar');
    Route::get('/analisis/{analisis}/pdf', [AnalisisController::class, 'download'])->name('analisis.pdf');

    // Panel de insignias de cumplimiento
Route::get('/insignias', function () {
    return view('insignias');
})->name('insignias.index');
    });