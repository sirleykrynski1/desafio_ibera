<?php

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