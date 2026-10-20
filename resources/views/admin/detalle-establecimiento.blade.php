@extends('layouts.app')

@section('content')
<div class="py-2">
            
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.detalle-establecimientos') }}" class="text-decoration-none text-secondary mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Regresar</a>
            <h2 class="fw-bold text-success mb-0">{{ $establecimiento->nombre }}</h2>
            <p class="text-muted"><i class="bi bi-geo-alt-fill"></i> {{ $establecimiento->ubicacion }} | CUIT: {{ $establecimiento->cuit }}</p>
        </div>
        <div class="text-end">
            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill"><i class="bi bi-shield-check me-1"></i> Permiso Activo</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- COLUMNA IZQUIERDA: DATOS DEL ESTABLECIMIENTO -->
        <div class="col-lg-4">
            <div class="card card-custom p-4 h-100">
                <h5 class="fw-bold mb-4 border-bottom pb-2">Datos Técnicos</h5>
                
                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 11px;">Rubro</small>
                    <span class="fs-6">{{ ucfirst($establecimiento->rubro) }}</span>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 11px;">Destino del Efluente</small>
                    <span class="fs-6 badge bg-info text-dark rounded-pill">{{ str_replace('_', ' ', ucfirst($establecimiento->tipo_destino_vuelco)) }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 11px;">Capacidad Máxima</small>
                    <span class="fs-6">{{ $establecimiento->capacidad_maxima ?? 0 }} personas</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 11px;">Volumen Biodigestor</small>
                    <span class="fs-6">{{ $establecimiento->capacidad_biodigestor ?? 0 }} Litros</span>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: ALERTAS E HISTORIAL -->
        <div class="col-lg-8">
            
            <!-- ALERTAS -->
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="d-flex">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-warning me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Alertas Activas</h6>
                        @if(!empty($alertas_establecimiento) && count($alertas_establecimiento) > 0)
                            @foreach($alertas_establecimiento as $alerta)
                                <p class="mb-1 small">• {{ $alerta->mensaje ?? $alerta->descripcion ?? 'Alerta climática registrada en la zona.' }}</p>
                            @endforeach
                        @else
                            <p class="mb-0 small">No hay alertas climáticas vigentes pendientes de revisión para este establecimiento.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- HISTORIAL DE ANÁLISIS -->
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Historial de Análisis</h5>
                    <a href="{{ route('icaa.exportar') }}" class="btn btn-sm btn-outline-success rounded-pill"><i class="bi bi-download me-1"></i> Descargar Reporte</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle text-center">
                        <thead class="table-light">
                            <tr style="font-size: 13px;">
                                <th>Fecha</th>
                                <th>Laboratorio</th>
                                <th>Resultado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historial_analisis as $analisis)
                                @php
                                    $estadoRaw = strtolower((string) ($analisis->resultado_final ?? $analisis->dictamen_final ?? $analisis->resultado_sugerido ?? $analisis->resultado ?? 'pendiente'));
                                    $badgeClass = match(true) {
                                        in_array($estadoRaw, ['aprobado', 'cumple', 'en_norma']) => 'bg-success-subtle text-success',
                                        $estadoRaw === 'alerta' => 'bg-warning-subtle text-warning-emphasis',
                                        in_array($estadoRaw, ['rechazado', 'incumple']) => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary',
                                    };
                                    $idAnalisis = $analisis->id ?? $analisis->id_analisis ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $analisis->fecha_muestra ?? $analisis->fecha ?? $analisis->created_at ?? '-' }}</td>
                                    <td>{{ $analisis->laboratorio ?? 'Laboratorio oficial' }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1">
                                            {{ ucfirst($estadoRaw) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($idAnalisis)
                                            <a href="{{ route('analisis.mostrar', $idAnalisis) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Ver estudio
                                            </a>
                                        @else
                                            <span class="text-muted small">{{ $analisis->observaciones_revision ?? 'Registrado' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted py-4">No hay análisis registrados para este establecimiento.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
