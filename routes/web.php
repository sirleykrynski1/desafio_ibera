<?php

use Illuminate\Support\Facades\Route;

// Redirige la página principal directo al dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Carga la vista que acabás de crear
Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/analisis', function () {
    return view('cargar_analisis');
});

Route::get('/historial', function () {
    return view('historial');
});
