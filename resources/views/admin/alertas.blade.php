<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Alertas - ICAA Efluentes</title>
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
        <!-- BARRA LATERAL -->
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
                    <a href="{{ url('/icaa/dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/icaa/alertas') }}" class="nav-link active"><i class="bi bi-bell-fill me-2"></i> Alertas</a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/icaa/inspectores') }}" class="nav-link"><i class="bi bi-building me-2"></i> Establecimientos</a>
                </li>
            </ul>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Centro de Alertas</h2>
                    <p class="text-muted">Monitoreo de riesgos climáticos y saturación de efluentes.</p>
                </div>
                <div>
                    <span class="badge bg-danger rounded-pill px-3 py-2 fs-6"><i class="bi bi-exclamation-octagon me-1"></i> {{ count($alertas) }} Activas</span>
                </div>
            </div>

            <!-- LISTADO DE ALERTAS -->
            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-4 border-bottom pb-3">Prioridad de Inspección</h5>

                <div class="d-flex flex-column gap-3">
                    @foreach($alertas as $alerta)
                        <div class="alert border border-{{ $alerta->color }}-subtle bg-white shadow-sm rounded-4 mb-0 d-flex align-items-center justify-content-between p-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-{{ $alerta->color }}-subtle p-3 rounded-circle me-3">
                                    <i class="bi {{ $alerta->icono }} text-{{ $alerta->color }} fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">
                                        {{ $alerta->establecimiento }} 
                                        <span class="text-muted fw-normal mx-1">-</span> 
                                        {{ $alerta->mensaje }}
                                    </h6>
                                    <div class="d-flex gap-3 small mt-1">
                                        <span class="text-muted"><i class="bi bi-clock-history me-1"></i> Último análisis: <strong>{{ $alerta->ultimo_analisis }}</strong></span>
                                        <span class="text-{{ $alerta->color }}"><i class="bi bi-flag-fill me-1"></i> Prioridad: <strong>{{ $alerta->prioridad }}</strong></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- BOTÓN MARCAR COMO REVISADA -->
                            <div>
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
                            <p>No hay alertas climáticas ni de riesgo registradas en el sistema.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>