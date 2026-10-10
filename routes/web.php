<?php

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
