@extends('layouts.app')

@section('content')
<div class="row mt-4">
    <!-- Columna Izquierda: Semáforo e Insignias -->
    <div class="col-lg-8">
        <h2 class="mb-4">Resumen del Establecimiento</h2>
        
        <!-- Tarjeta del Semáforo -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body text-center p-5">
                <h4 class="text-muted mb-4">Estado Actual de Cumplimiento</h4>
                
                <div class="alert alert-success py-4 rounded-4">
                    <h1 class="display-4 fw-bold mb-0">VERDE</h1>
                    <p class="lead mt-2 mb-0">El establecimiento cumple con la normativa.</p>
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <p class="mb-1">Próximo vencimiento del permiso de vuelco:</p>
                    <h5 class="fw-bold">15 de Noviembre, 2026</h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Insignias (Gamificación) -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold border-bottom pb-2">Insignias de Cumplimiento</h5>
                
                <!-- Caso 1: Tiene la insignia (Mostramos esto de ejemplo) -->
                <div class="d-flex align-items-center mt-3 p-3 bg-light rounded">
                    <div class="bg-warning text-dark rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 60px; height: 60px;">
                        <span class="fs-2">⭐</span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold text-success">Sello "Recomendado" Eco-Iberá</h6>
                        <p class="text-muted mb-0 small">¡Felicitaciones! Tienes 6 o más meses de cumplimiento continuo.</p>
                    </div>
                </div>

                <!-- Caso 2: Le falta para la insignia (Comentado para cuando conecten el backend) -->
                <!--
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle me-2"></i> Te faltan <strong>2 meses</strong> de cumplimiento continuo para obtener tu próxima insignia.
                </div>
                -->
            </div>
        </div>

        <!-- Tarjeta: Subir Análisis -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h5 class="card-title fw-bold mb-1">Subir Análisis de Laboratorio</h5>
                    <p class="text-muted mb-0 small">Cargá el informe PDF de tu establecimiento para que sea evaluado.</p>
                </div>
                <a href="{{ route('analisis.crear') }}" class="btn btn-success btn-lg px-4" id="btn-subir-analisis">
                    📄 Subir Análisis
                </a>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Alertas Climáticas -->
    <div class="col-lg-4 mt-4 mt-lg-0">
        <h2 class="mb-4 h5 text-muted text-uppercase fw-bold pt-2">Avisos del ICAA</h2>
        
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body p-4">
                
                <!-- Alerta 1 -->
                <div class="alert alert-danger border-0 border-start border-4 border-danger bg-white shadow-sm mb-3">
                    <div class="d-flex justify-content-between">
                        <strong class="text-danger">Lluvia Intensa (Iberá)</strong>
                        <small class="text-muted">Hace 2h</small>
                    </div>
                    <p class="small mb-0 mt-1">Prioridad Alta: Riesgo de saturación en pozos absorbentes.</p>
                </div>

                <!-- Alerta 2 -->
                <div class="alert alert-warning border-0 border-start border-4 border-warning bg-white shadow-sm mb-3">
                    <div class="d-flex justify-content-between">
                        <strong class="text-warning text-dark">Tormenta Eléctrica</strong>
                        <small class="text-muted">Ayer</small>
                    </div>
                    <p class="small mb-0 mt-1">Prioridad Media: Posibles cortes de energía en la zona.</p>
                </div>
                
                <!-- Alerta 3 -->
                <div class="alert alert-secondary border-0 border-start border-4 border-secondary bg-white shadow-sm mb-0">
                    <div class="d-flex justify-content-between">
                        <strong class="text-secondary">Crecida del Río</strong>
                        <small class="text-muted">Hace 3 días</small>
                    </div>
                    <p class="small mb-0 mt-1">Informativo: Niveles dentro de los parámetros normales.</p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection