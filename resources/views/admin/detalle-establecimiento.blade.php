<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle: {{ $establecimiento->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --primary-color: #1b5e20; --secondary-color: #2e7d32; --dark-sidebar: #0a2512; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: var(--dark-sidebar); min-height: 100vh; color: #fff; }
        .sidebar .nav-link { color: #b2dfdb; border-radius: 8px; margin: 4px 15px; padding: 10px 15px; }
        .sidebar .nav-link.active { background-color: var(--secondary-color); color: #fff; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #fff; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- BARRA LATERAL (Sidebar mínima) -->
        <div class="col-md-3 col-lg-2 px-0 sidebar d-flex flex-column pb-4">
            <div class="px-4 py-4 d-flex align-items-center">
                <i class="bi bi-droplet-fill text-success fs-3 me-2"></i>
                <div>
                    <h6 class="mb-0 fw-bold">ICAA</h6>
                    <small class="text-muted text-uppercase" style="font-size: 10px;">Desafío Iberá</small>
                </div>
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a href="{{ url('/icaa/inspectores') }}" class="nav-link"><i class="bi bi-arrow-left me-2"></i> Volver al panel</a>
                </li>
            </ul>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-md-9 col-lg-10 p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ url('/icaa/inspectores') }}" class="text-decoration-none text-secondary mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Regresar</a>
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
                                <p class="mb-0 small">No se registran alertas climáticas ni de rebosamiento en esta zona en los últimos 7 días.</p>
                            </div>
                        </div>
                    </div>

                    <!-- HISTORIAL DE ANÁLISIS -->
                    <div class="card card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Historial de Análisis</h5>
                            <button class="btn btn-sm btn-outline-success rounded-pill"><i class="bi bi-download me-1"></i> Descargar Reporte</button>
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
                                    @if(empty($historial_analisis))
                                    <tr>
                                        <td colspan="4" class="text-muted py-4">Aún no hay análisis cargados (Esperando integración de la base de datos).</td>
                                    </tr>
                                    @else
                                        <!-- Acá irá el foreach cuando se integre con la Persona 1 -->
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>