<?php

use App\Http\Controllers\AlertaClimaticaController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\ClimaEstablecimientoController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\PanelGestionController;
use App\Http\Controllers\PermisoVuelcoController;
use App\Http\Controllers\RevisionAnalisisController;
use App\Http\Controllers\SesionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'inicio')->name('inicio');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SesionController::class, 'create'])->name('login');
    Route::post('/login', [SesionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::post('/registro', [SesionController::class, 'register'])->middleware('throttle:6,1')->name('registro');
});
Route::middleware('auth')->group(function (): void {
    Route::get('/alertas-climaticas', [AlertaClimaticaController::class, 'index'])->name('alertas.indice');
    Route::patch('/alertas-climaticas/{alerta}', [AlertaClimaticaController::class, 'update'])->name('alertas.revisar');
    Route::post('/logout', [SesionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [PanelGestionController::class, 'show'])->name('dashboard');
    Route::get('/gestion', [PanelGestionController::class, 'index'])->name('gestion');
    Route::get('/establecimientos/{establecimiento}/clima', [ClimaEstablecimientoController::class, 'show'])->middleware('throttle:20,1')->name('establecimientos.clima');
    Route::post('/analisis/{analisis}/revision', [RevisionAnalisisController::class, 'store'])->name('analisis.revision');
    Route::get('/establecimientos', [EstablecimientoController::class, 'index'])->name('establecimientos.indice');
    Route::get('/establecimientos/crear', [EstablecimientoController::class, 'create'])->name('establecimientos.crear');
    Route::post('/establecimientos', [EstablecimientoController::class, 'store'])->name('establecimientos.guardar');
    Route::get('/establecimientos/{establecimiento}', [EstablecimientoController::class, 'show'])->name('establecimientos.mostrar');
    Route::get('/establecimientos/{establecimiento}/editar', [EstablecimientoController::class, 'edit'])->name('establecimientos.editar');
    Route::put('/establecimientos/{establecimiento}', [EstablecimientoController::class, 'update'])->name('establecimientos.actualizar');
    Route::post('/establecimientos/{establecimiento}/permiso', [PermisoVuelcoController::class, 'store'])->name('permisos.guardar');
    Route::get('/analisis', [AnalisisController::class, 'create'])->name('analisis.crear');
    Route::post('/analisis', [AnalisisController::class, 'store'])->middleware('throttle:10,1')->name('analisis.guardar');
    Route::get('/historial', [AnalisisController::class, 'index'])->name('analisis.indice');
    Route::get('/analisis/{analisis}', [AnalisisController::class, 'show'])->name('analisis.mostrar');
    Route::get('/analisis/{analisis}/pdf', [AnalisisController::class, 'download'])->name('analisis.pdf');
});

// ==========================================
// RUTAS EXCLUSIVAS DEL PANEL ICAA (INSPECTOR)
// ==========================================

Route::get('/icaa/inspectores', [App\Http\Controllers\Admin\InspectorController::class, 'index'])->name('icaa.inspectores');

Route::post('/icaa/establecimientos', [App\Http\Controllers\Admin\InspectorController::class, 'store'])->name('icaa.establecimientos.guardar');

Route::get('/icaa/cargar-analisis', [App\Http\Controllers\Admin\InspectorController::class, 'createAnalisis'])->name('icaa.analisis.crear');

Route::post('/icaa/cargar-analisis', [App\Http\Controllers\Admin\InspectorController::class, 'storeAnalisis'])->name('icaa.analisis.guardar');

// Ruta para actualizar un establecimiento
Route::put('/icaa/establecimientos/{id}', [App\Http\Controllers\Admin\InspectorController::class, 'updateEstablecimiento'])->name('icaa.establecimientos.actualizar');

// Ruta para eliminar un establecimiento
Route::delete('/icaa/establecimientos/{id}', [App\Http\Controllers\Admin\InspectorController::class, 'destroyEstablecimiento'])->name('icaa.establecimientos.eliminar');

Route::get('/icaa/establecimientos/{id}', [App\Http\Controllers\Admin\InspectorController::class, 'show'])->name('icaa.establecimientos.ver');

Route::get('/icaa/dashboard', [App\Http\Controllers\Admin\InspectorController::class, 'dashboard'])->name('icaa.dashboard');

Route::get('/icaa/alertas', [App\Http\Controllers\Admin\InspectorController::class, 'alertas'])->name('icaa.alertas');

Route::get('/icaa/exportar-reporte', [App\Http\Controllers\Admin\InspectorController::class, 'exportarCsv'])->name('icaa.exportar');