@extends('layouts.app')

@section('title', 'Dashboard Gobierno - Panel Ambiental')

@section('content')
<div class="row g-4">
    <!-- Encabezado de bienvenida -->
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-2 border-bottom border-success-subtle">
            <div>
                <h1 class="h3 text-success fw-bold mb-1">
                    Panel de Control Ambiental 🐊
                </h1>
                <p class="text-muted mb-0">
                    Monitoreo en tiempo real de fosa séptica, drenajes y riesgo de lixiviados en el Parque Iberá.
                </p>
            </div>
            <div>
                <span class="badge bg-success bg-gradient px-3 py-2 fs-6 shadow-sm">
                    🌿 Estado Humedal: Óptimo
                </span>
            </div>
        </div>
    </div>

    <!-- Tarjetas de métricas rápidas -->
    <div class="col-md-3">
        <div class="card card-eco shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small fw-semibold">Hoteles Activos</h6>
                        <h3 class="fw-bold text-success mb-0">24</h3>
                    </div>
                    <div class="fs-1">🦦</div>
                </div>
                <small class="text-success fw-semibold"><i class="bi bi-arrow-up-short"></i> 100% registrados</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-eco shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small fw-semibold">Alertas de Riesgo</h6>
                        <h3 class="fw-bold text-danger mb-0">2</h3>
                    </div>
                    <div class="fs-1">🦩</div>
                </div>
                <small class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle"></i> Revisión urgente</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-eco shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small fw-semibold">Mantenimientos Mes</h6>
                        <h3 class="fw-bold text-success mb-0">48</h3>
                    </div>
                    <div class="fs-1">🐸</div>
                </div>
                <small class="text-muted">Limpiezas certificadas</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-eco shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small fw-semibold">Índice Ambiental</h6>
                        <h3 class="fw-bold text-success mb-0">94%</h3>
                    </div>
                    <div class="fs-1">💧</div>
                </div>
                <small class="text-success fw-semibold"><i class="bi bi-shield-check"></i> Cumplimiento alto</small>
            </div>
        </div>
    </div>

    <!-- Panel principal con el mapa interactivo (Placeholder) -->
    <div class="col-12">
        <div class="card card-eco shadow-sm">
            <div class="card-header card-eco-header d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <span>🐊</span> Mapa Interactivo de Monitoreo Geográfico y Lixiviados
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success">
                        <i class="bi bi-layers"></i> Capas de Sensores
                    </button>
                    <button class="btn btn-sm btn-eco">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar Datos
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Div Placeholder donde se integrará Leaflet / Google Maps / Mapbox -->
                <div class="map-placeholder text-center p-5">
                    <div class="mb-3">
                        <span class="display-4">🗺️ 🐊</span>
                    </div>
                    <h5 class="fw-bold text-success">Espacio para Mapa Interactivo SIG</h5>
                    <p class="text-muted max-w-md mx-auto mb-3" style="max-width: 550px;">
                        Aquí se renderizará el mapa de calor georreferenciado con los niveles de saturación de fosas sépticas, napas freáticas y estado de vertidos en la cuenca de los Esteros del Iberá.
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                            📍 24 Puntos de Control
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">
                            ⚠️ 2 Puntos en Nivel Crítico
                        </span>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2">
                            💧 Capa Hidrológica Activa
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
