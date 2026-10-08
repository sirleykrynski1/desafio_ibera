<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/estado', function (): JsonResponse {
    return response()->json([
        'mensaje' => 'La API de Desafío Iberá está funcionando',
    ]);
});
