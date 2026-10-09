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
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Landing general (Pública - Presenta el proyecto Ypora)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas que requieren estar logueado (Sin importar el rol)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard general: aquí podrías redirigir al dashboard específico según quién entra
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==========================================================
    // GRUPO HERMÉTICO: GOBIERNO E INSPECTORES
    // ==========================================================
    Route::middleware(['role:inspector,admin_gobierno'])->group(function () {
        
        Route::get('/analisis', function () {
            return view('cargar_analisis');
        })->name('analisis.cargar');

        Route::get('/historial', function () {
            return view('historial');
        })->name('analisis.historial');
        
        // Más adelante, aquí también irán las rutas del parser OCR de PDFs
    });

    // ==========================================================
    // GRUPO HERMÉTICO: ESTABLECIMIENTOS (DUEÑOS)
    // ==========================================================
    Route::middleware(['role:establecimiento'])->group(function () {
        // Rutas exclusivas para que los establecimientos gestionen sus efluentes
        // Route::get('/mis-reportes', ...
    });
});

require __DIR__.'/auth.php';
