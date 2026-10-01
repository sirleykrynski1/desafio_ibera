<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\MaintenanceLogController;
use App\Http\Controllers\OccupancyLogController;
use Illuminate\Support\Facades\Route;

// Dominio base extraído de APP_URL en .env (ej. 'desafio-ibera.test' o 'ecoibera.com')
$baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'desafio-ibera.test';

/*
|--------------------------------------------------------------------------
| 1. SUBDOMINIO PRIVADO: GOBIERNO E INSPECTORES
| URL: http://gobierno.desafio-ibera.test
|--------------------------------------------------------------------------
*/
Route::domain("gobierno.{$baseDomain}")->as('gobierno.')->group(function () {

    // Rutas protegidas exclusivamente para admin_gobierno e inspectores
    Route::middleware(['auth', 'role:admin_gobierno,inspector'])->group(function () {
        // Centro de Monitoreo General y Mapa Provincial de Riesgos
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Gestión global de establecimientos (auditoría e inspecciones)
        Route::resource('establishments', EstablishmentController::class)->only(['index', 'show']);

        // Auditoría de Mantenimientos: Revisión y Aprobación/Rechazo de comprobantes
        Route::get('maintenance-logs', [MaintenanceLogController::class, 'index'])->name('maintenance-logs.index');
        Route::patch('maintenance-logs/{maintenanceLog}/status', [MaintenanceLogController::class, 'updateStatus'])
            ->name('maintenance-logs.updateStatus');

        // Historial general de ocupaciones reportadas
        Route::get('occupancy-logs', [OccupancyLogController::class, 'index'])->name('occupancy-logs.index');
    });
});

/*
|--------------------------------------------------------------------------
| 2. DOMINIO / SUBDOMINIO PÚBLICO: PROPIETARIOS Y COMERCIOS
| URL: http://desafio-ibera.test (o app.desafio-ibera.test)
|--------------------------------------------------------------------------
*/
Route::domain($baseDomain)->group(function () {

    // Landing page pública
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    // Rutas del portal privado de Propietarios (Hoteles, Gastronomía, Comercios)
    Route::middleware(['auth', 'role:propietario,admin_gobierno'])->group(function () {
        // Panel del Propietario (Semáforo de sus establecimientos e insignias)
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD completo de sus propios establecimientos
        Route::resource('establishments', EstablishmentController::class);

        // Registro de ocupación de huéspedes
        Route::resource('occupancy-logs', OccupancyLogController::class)->only(['index', 'create', 'store']);

        // Registro de mantenimiento y carga de tickets/facturas de desagote
        Route::resource('maintenance-logs', MaintenanceLogController::class)->only(['index', 'create', 'store']);
    });
});
