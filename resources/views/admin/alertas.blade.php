@extends('layouts.app')

@section('content')
<div class="py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Centro de Alertas</h2>
            <p class="text-muted mb-0 small">Monitoreo de riesgos climáticos y saturación de efluentes en tiempo real.</p>
        </div>
        <div>
            <span class="badge bg-danger rounded-pill px-3 py-2 fs-6 shadow-sm"><i class="bi bi-exclamation-octagon me-1"></i> {{ count($alertas) }} Activas</span>
        </div>
    </div>

    <!-- LISTADO DE ALERTAS -->
    <div class="card card-custom p-4">
        <h5 class="fw-bold mb-4 border-bottom pb-3">Prioridad de Inspección</h5>

        <div class="d-flex flex-column gap-3">
            @foreach($alertas as $alerta)
                <div class="alert border border-{{ $alerta->color }}-subtle bg-white shadow-sm rounded-4 mb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 gap-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-{{ $alerta->color }}-subtle p-3 rounded-circle me-3 flex-shrink-0">
                            <i class="bi {{ $alerta->icono }} text-{{ $alerta->color }} fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">
                                {{ $alerta->establecimiento }} 
                                <span class="text-muted fw-normal mx-1">-</span> 
                                {{ $alerta->mensaje }}
                            </h6>
                            <div class="d-flex flex-wrap gap-3 small mt-1">
                                <span class="text-muted"><i class="bi bi-clock-history me-1"></i> Último análisis: <strong>{{ $alerta->ultimo_analisis }}</strong></span>
                                <span class="text-{{ $alerta->color }}"><i class="bi bi-flag-fill me-1"></i> Prioridad: <strong>{{ $alerta->prioridad }}</strong></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- BOTÓN MARCAR COMO REVISADA -->
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold shadow-sm">
                            <i class="bi bi-check2-all me-1"></i> Marcar como revisada
                        </button>
                    </div>
                </div>
            @endforeach

            @if(empty($alertas))
                <div class="text-center text-muted py-5">
                    <i class="bi bi-shield-check fs-1 text-success opacity-50 mb-3 d-block"></i>
                    <h5 class="fw-bold">Todo en orden</h5>
                    <p class="mb-0">No hay alertas climáticas ni de riesgo registradas en el sistema.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection