<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ICAA Efluentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --primary-color: #1b5e20; --secondary-color: #2e7d32; --dark-sidebar: #0a2512; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: var(--dark-sidebar); min-height: 100vh; color: #fff; }
        .sidebar .nav-link { color: #b2dfdb; border-radius: 8px; margin: 4px 15px; padding: 10px 15px; }
        .sidebar .nav-link.active { background-color: var(--secondary-color); color: #fff; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #fff; }
        .metric-number { font-size: 2.5rem; font-weight: 800; line-height: 1; }
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
                    <a href="{{ url('/icaa/dashboard') }}" class="nav-link active"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/icaa/inspectores') }}" class="nav-link"><i class="bi bi-building me-2"></i> Establecimientos</a>
                </li>
                <!-- El resto de los links se pueden ir agregando -->
            </ul>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-md-9 col-lg-10 p-4">
            <h2 class="fw-bold mb-4">Visión General del Iberá</h2>

            <!-- TARJETAS DE MÉTRICAS PRINCIPALES -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card card-custom p-4 h-100 border-start border-4 border-primary">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-bold text-uppercase">Total Registrados</small>
                            <i class="bi bi-buildings fs-4 text-primary opacity-50"></i>
                        </div>
                        <div class="metric-number text-dark">{{ $total_establecimientos }}</div>
                        <small class="text-muted mt-2 d-block">Establecimientos locales</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card card-custom p-4 h-100 border-start border-4 border-success">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-bold text-uppercase">En Norma</small>
                            <i class="bi bi-check-circle-fill fs-4 text-success opacity-50"></i>
                        </div>
                        <div class="metric-number text-success">{{ $en_norma }}</div>
                        <small class="text-muted mt-2 d-block">Parámetros correctos</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-custom p-4 h-100 border-start border-4 border-warning">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-bold text-uppercase">En Alerta</small>
                            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning opacity-50"></i>
                        </div>
                        <div class="metric-number text-warning">{{ $en_alerta }}</div>
                        <small class="text-muted mt-2 d-block">Límites cercanos</small>
                    </div>
                </div>

                <div class="col-md-3">
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
                <!-- PANEL DE ALERTAS CLIMÁTICAS -->
                <div class="col-lg-6">
                    <div class="card card-custom p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Alertas Recientes</h5>
                            <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill">Ver todas</a>
                        </div>
                        
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-3" role="alert">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-1"><i class="bi bi-cloud-rain-heavy-fill me-2"></i>Lluvia intensa en el Iberá</h6>
                                    <p class="mb-0 small">Los pozos de 3 hoteles pueden saturarse. Prioridad: Alta.</p>
                                </div>
                                <button class="btn btn-sm btn-danger rounded-pill px-3">Revisar</button>
                            </div>
                        </div>

                        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-0" role="alert">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-1"><i class="bi bi-thermometer-sun me-2"></i>Alerta de Temperatura</h6>
                                    <p class="mb-0 small">Posible evaporación acelerada en lagunas de estabilización.</p>
                                </div>
                                <button class="btn btn-sm btn-warning rounded-pill px-3">Revisar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RANKING DE RECOMENDADOS -->
                <div class="col-lg-6">
                    <div class="card card-custom p-4 h-100 bg-success text-white position-relative overflow-hidden">
                        <div class="position-absolute top-0 end-0 p-4 opacity-25">
                            <i class="bi bi-award-fill" style="font-size: 8rem;"></i>
                        </div>
                        <div class="position-relative z-index-1">
                            <h5 class="fw-bold mb-3"><i class="bi bi-star-fill me-2 text-warning"></i> Ranking Recomendados</h5>
                            <p class="small mb-4 text-light">Establecimientos con más de 6 meses de cumplimiento continuo.</p>
                            
                            <ul class="list-group list-group-flush rounded-3 text-dark">
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-white">
                                    <div class="fw-bold">1. Ecoposada del Estero</div>
                                    <span class="badge bg-success rounded-pill">12 meses</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-white">
                                    <div class="fw-bold">2. Hostería Rincón</div>
                                    <span class="badge bg-success rounded-pill">8 meses</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-white text-muted">
                                    <small>Esperando conexión con base de datos...</small>
                                </li>
                            </ul>
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