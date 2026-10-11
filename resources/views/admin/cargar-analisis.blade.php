<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICAA - Cargar Análisis de Laboratorio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1b5e20;
            --secondary-color: #2e7d32;
            --dark-sidebar: #0a2512;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background-color: var(--dark-sidebar);
            min-height: 100vh;
            color: #fff;
        }
        .sidebar .nav-link {
            color: #b2dfdb;
            border-radius: 8px;
            margin: 4px 15px;
            padding: 10px 15px;
        }
        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
            color: #fff;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            background: #fff;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- BARRA LATERAL -->
        <div class="col-md-3 col-lg-2 px-0 sidebar d-flex flex-column justify-content-between pb-4">
            <div>
                <div class="px-4 py-4 d-flex align-items-center">
                    <i class="bi bi-droplet-fill text-success fs-3 me-2"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Gestión de Efluentes</h6>
                        <small class="text-muted text-uppercase" style="font-size: 10px;">ICAA - Corrientes</small>
                    </div>
                </div>

                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a href="{{ url('/icaa/inspectores') }}" class="nav-link"><i class="bi bi-house-door me-2"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="bi bi-file-earmark-text me-2"></i> Inspecciones</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('analisis.crear') }}" class="nav-link active"><i class="bi bi-upload me-2"></i> Cargar análisis</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/icaa/inspectores') }}" class="nav-link"><i class="bi bi-building me-2"></i> Establecimientos</a>
                    </li>
                </ul>
            </div>

            <div class="px-4 border-top pt-3 border-secondary">
                <div class="d-flex align-items-center">
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <span class="text-white fw-bold">II</span>
                    </div>
                    <div>
                        <p class="mb-0 fw-bold small">Inspector ICAA</p>
                        <small class="text-muted">Desafío Iberá</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold">Cargar análisis de laboratorio</h2>
                    <p class="text-muted">Registro oficial de muestras y resultados de efluentes.</p>
                </div>
            </div>

            <!-- FORMULARIO OFICIAL -->
            <div class="card card-custom p-4 mb-4">
                <form action="{{ route('analisis.guardar') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Establecimiento Auditado</label>
                        <select name="establecimiento_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione el establecimiento --</option>
                            @foreach($establecimientos as $est)
                                <option value="{{ $est->id }}">{{ $est->nombre }} ({{ $est->ubicacion }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Fecha de la Muestra</label>
                            <input type="date" name="fecha_muestra" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Laboratorio Emisor</label>
                            <input type="text" name="laboratorio" class="form-control" required placeholder="Ej: Laboratorio H2O Corrientes">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-semibold small">Resultado Sugerido</label>
                            <select name="resultado_sugerido" class="form-select" required>
                                <option value="apto">Apto / En Norma</option>
                                <option value="observado">Observado</option>
                                <option value="no_apto">No Apto</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-semibold small">Estado</label>
                            <select name="estado" class="form-select" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-semibold small">Resultado Final</label>
                            <select name="resultado_final" class="form-select" required>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Ruta del PDF (Opcional)</label>
                            <input type="text" name="ruta_pdf" class="form-control" placeholder="Ej: /pdfs/analisis_1.pdf">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Revisado Por</label>
                            <input type="text" name="revisado_por" class="form-control" value="Inspector ICAA" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small">Observaciones de Revisión</label>
                        <textarea name="observaciones_revision" class="form-control" rows="3" placeholder="Detalles sobre los parámetros o el estado del efluente..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ url('/icaa/inspectores') }}" class="btn btn-outline-secondary rounded-pill fw-bold px-4">Cancelar</a>
                        <button type="submit" class="btn btn-success rounded-pill fw-bold px-4">Guardar Análisis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>