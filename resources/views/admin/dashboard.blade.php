@extends('layouts.app')

@section('content')
<div class="py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Visión General del Iberá</h2>
            <p class="text-muted mb-0 small">Evaluación automática del último análisis de cada establecimiento. El dictamen del inspector se consulta por separado.</p>
            <p class="text-muted mb-0 small">Sin evaluación automática disponible: {{ $sin_evaluacion }}</p>
        </div>
        <div>
            <a href="{{ route('admin.detalle-establecimientos') }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="bi bi-building me-1"></i> Ver Establecimientos
            </a>
        </div>
    </div>

    <!-- TARJETAS DE MÉTRICAS PRINCIPALES -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-4 h-100 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold text-uppercase">Total Registrados</small>
                    <i class="bi bi-buildings fs-4 text-primary opacity-50"></i>
                </div>
                <div class="metric-number text-dark">{{ $total_establecimientos }}</div>
                <small class="text-muted mt-2 d-block">Establecimientos locales</small>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-4 h-100 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold text-uppercase">En Norma</small>
                    <i class="bi bi-check-circle-fill fs-4 text-success opacity-50"></i>
                </div>
                <div class="metric-number text-success">{{ $en_norma }}</div>
                <small class="text-muted mt-2 d-block">Parámetros correctos</small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-4 h-100 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold text-uppercase">En Alerta</small>
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-warning opacity-50"></i>
                </div>
                <div class="metric-number text-warning">{{ $en_alerta }}</div>
                <small class="text-muted mt-2 d-block">Límites cercanos</small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-4 h-100 border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold text-uppercase">Incumplimiento</small>
                    <i class="bi bi-x-circle-fill fs-4 text-danger opacity-50"></i>
                </div>
                <div class="metric-number text-danger">{{ $incumplen }}</div>
                <small class="text-muted mt-2 d-block">Requieren inspección</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- PANEL DE ALERTAS CLIMÁTICAS RECIENTES (DATOS REALES) -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Alertas Recientes</h5>
                    <a href="{{ route('admin.alertas') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Ver todas</a>
                </div>
                
                @forelse($alertas_recientes ?? [] as $alerta)
                    <div class="alert alert-{{ $alerta->color ?? 'warning' }} border-0 shadow-sm rounded-4 mb-3" role="alert">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    <i class="bi {{ $alerta->icono ?? 'bi-cloud-rain-heavy-fill' }} me-2"></i>{{ $alerta->establecimiento }}
                                </h6>
                                <p class="mb-0 small">{{ $alerta->mensaje }} (Prioridad: {{ $alerta->prioridad }})</p>
                            </div>
                            <a href="{{ route('admin.alertas') }}" class="btn btn-sm btn-{{ $alerta->color ?? 'warning' }} rounded-pill px-3">Revisar</a>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light border shadow-sm rounded-4 mb-0 text-muted" role="alert">
                        <i class="bi bi-check-circle text-success me-2"></i>No hay alertas climáticas activas registradas en este momento.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RANKING DE RECOMENDADOS (DATOS REALES) -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 h-100 bg-success text-white position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-4 opacity-25">
                    <i class="bi bi-award-fill" style="font-size: 8rem;"></i>
                </div>
                <div class="position-relative z-index-1">
                    <h5 class="fw-bold mb-3"><i class="bi bi-star-fill me-2 text-warning"></i> Establecimientos en Norma</h5>
                    <p class="small mb-4 text-light">Último análisis con evaluación automática favorable; no implica aprobación del inspector.</p>
                    
                    <ul class="list-group list-group-flush rounded-3 text-dark">
                        @forelse($recomendados ?? [] as $index => $est)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-white">
                                <div class="fw-bold">{{ $index + 1 }}. {{ $est->nombre }}</div>
                                <span class="badge bg-success rounded-pill">En norma</span>
                            </li>
                        @empty
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-white text-muted">
                                <small>Aún no hay establecimientos con evaluación automática favorable.</small>
                            </li>
                        @endforelse
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-white text-muted">
                            <small>Monitoreo continuo activo</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
