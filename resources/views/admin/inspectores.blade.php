<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICAA - Gestión de Efluentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1b5e20;
            --secondary-color: #2e7d32;
            --accent-green: #e8f5e9;
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
        <!-- BARRA LATERAL (Sidebar) -->
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
                        <a href="{{ url('/icaa/dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/icaa/alertas') }}" class="nav-link"><i class="bi bi-bell-fill me-2"></i> Alertas</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/icaa/inspectores') }}" class="nav-link active"><i class="bi bi-building me-2"></i> Establecimientos</a>
                    </li>
                </ul>
            </div>

            <!-- Usuario del ICAA -->
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
                    <h2 class="fw-bold">¡Hola, Inspector ICAA!</h2>
                    <p class="text-muted">Acá podés gestionar las inspecciones y el cumplimiento ambiental en la región.</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-bell-fill fs-5 text-muted me-3"></i>
                    <div class="bg-white px-3 py-1 rounded-pill shadow-sm">
                        <span class="small fw-semibold text-muted">Inspector ICAA <i class="bi bi-chevron-down ms-1"></i></span>
                    </div>
                </div>
            </div>

            <!-- TARJETAS DE MÉTRICAS -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card card-custom p-3 d-flex flex-row align-items-center">
                        <div class="bg-success-subtle p-3 rounded-circle me-3"><i class="bi bi-check-circle-fill text-success fs-3"></i></div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Inspecciones en regla</small>
                            <h3 class="mb-0 fw-bold">85%</h3>
                            <small class="text-muted">de establecimientos en norma</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-3 d-flex flex-row align-items-center">
                        <div class="bg-primary-subtle p-3 rounded-circle me-3"><i class="bi bi-calendar2-event-fill text-primary fs-3"></i></div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Próximas auditorías</small>
                            <h3 class="mb-0 fw-bold">15 abr 2026</h3>
                            <small class="text-muted">Vencimiento de permisos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-3 d-flex flex-row align-items-center">
                        <div class="bg-danger-subtle p-3 rounded-circle me-3"><i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i></div>
                        <div>
                            <small class="text-muted d-block fw-semibold">Alertas de efluentes</small>
                            <h3 class="mb-0 fw-bold text-danger">Rebosamiento</h3>
                            <small class="text-muted">Lluvias intensas próximas</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILAS DE CONTENIDO: TABLA Y BANNER -->
            <div class="row g-4">
                
                <!-- COLUMNA IZQUIERDA (Filtros y Tabla) -->
                <div class="col-lg-8">
                    
                    <!-- SECCIÓN DE FILTROS -->
                    <div class="card card-custom p-3 mb-4 border-0 shadow-sm" style="background-color: #f1f8e9;">
                        <form action="{{ url('/icaa/inspectores') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label text-success fw-bold small mb-1"><i class="bi bi-geo-alt-fill me-1"></i>Zona / Ubicación</label>
                                <input type="text" name="zona" class="form-control border-success-subtle" placeholder="Ej: Iberá, Capital..." value="{{ request('zona') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-success fw-bold small mb-1"><i class="bi bi-water me-1"></i>Destino de Vuelco</label>
                                <select name="destino" class="form-select border-success-subtle">
                                    <option value="">Todos los destinos</option>
                                    <option value="cursos_agua" {{ request('destino') == 'cursos_agua' ? 'selected' : '' }}>Cursos de agua</option>
                                    <option value="laguna" {{ request('destino') == 'laguna' ? 'selected' : '' }}>Laguna</option>
                                    <option value="conducto_pluvial" {{ request('destino') == 'conducto_pluvial' ? 'selected' : '' }}>Cond. Pluvial</option>
                                    <option value="absorcion_suelo" {{ request('destino') == 'absorcion_suelo' ? 'selected' : '' }}>Suelo / Pozo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-success fw-bold small mb-1"><i class="bi bi-shield-check me-1"></i>Cumplimiento</label>
                                <select name="estado" class="form-select border-success-subtle">
                                    <option value="">Cualquier estado</option>
                                    <option value="verde" {{ request('estado') == 'verde' ? 'selected' : '' }}>🟢 En Norma (Verde)</option>
                                    <option value="amarillo" {{ request('estado') == 'amarillo' ? 'selected' : '' }}>🟡 Alerta (Amarillo)</option>
                                    <option value="rojo" {{ request('estado') == 'rojo' ? 'selected' : '' }}>🔴 Incumple (Rojo)</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-success fw-bold w-100 rounded-pill"><i class="bi bi-funnel-fill me-1"></i> Filtrar</button>
                                <a href="{{ url('/icaa/inspectores') }}" class="btn btn-outline-secondary fw-bold w-100 rounded-pill">Limpiar</a>
                            </div>
                        </form>
                    </div>

                    <!-- TARJETA DE LA TABLA -->
                    <div class="card card-custom p-4 border-0 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <h5 class="fw-bold mb-0">Establecimientos registrados</h5>
                            <div>
                                <!-- BOTÓN DESCARGAR REPORTE -->
                                <a href="{{ route('icaa.exportar') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold me-2">
                                    <i class="bi bi-filetype-csv me-1"></i> Descargar Reporte
                                </a>
                
                                <!-- BOTÓN NUEVO ESTABLECIMIENTO -->
                                <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoEstablecimiento">
                                    <i class="bi bi-plus-lg me-1"></i> Nuevo Establecimiento
                                </button>
                            </div>
                        </div>
                                
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr style="font-size: 13px;">
                                        <th>Establecimiento</th>
                                        <th>CUIT</th>
                                        <th>Ubicación</th>
                                        <th>Rubro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($establecimientos as $est)
                                    <tr>
                                        <td class="fw-bold text-success">{{ $est->nombre }}</td>
                                        <td>{{ $est->cuit }}</td>
                                        <td>{{ $est->ubicacion }}</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($est->rubro) }}</span></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <!-- BOTÓN VER DETALLE -->
                                                <a href="{{ route('icaa.establecimientos.ver', $est->id) }}" class="btn btn-sm btn-outline-info rounded-pill">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <!-- BOTÓN EDITAR -->
                                                 <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditar{{ $est->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <!-- BOTÓN ELIMINAR -->
                                                <form action="{{ route('icaa.establecimientos.eliminar', $est->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- VENTANA FLOTANTE (MODAL) PARA EDITAR -->
                                            <div class="modal fade" id="modalEditar{{ $est->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                                                        <div class="modal-header bg-primary text-white" style="border-radius: 16px 16px 0 0;">
                                                            <h1 class="modal-title fs-5 fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Establecimiento</h1>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        
                                                        <form action="{{ route('icaa.establecimientos.actualizar', $est->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            
                                                            <div class="modal-body p-4 text-start">
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label text-muted fw-semibold small">Nombre</label>
                                                                        <input type="text" name="nombre" class="form-control" value="{{ $est->nombre }}" required>
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label text-muted fw-semibold small">CUIT</label>
                                                                        <input type="number" name="cuit" class="form-control" value="{{ $est->cuit }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label text-muted fw-semibold small">Ubicación</label>
                                                                        <input type="text" name="ubicacion" class="form-control" value="{{ $est->ubicacion }}" required>
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label text-muted fw-semibold small">Rubro</label>
                                                                        <select name="rubro" class="form-select" required>
                                                                            <option value="gastronomico" {{ $est->rubro == 'gastronomico' ? 'selected' : '' }}>Gastronomía</option>
                                                                            <option value="hotel" {{ $est->rubro == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                                                            <option value="comercio" {{ $est->rubro == 'comercio' ? 'selected' : '' }}>Comercio</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label text-muted fw-semibold small">Cap. Máxima</label>
                                                                        <input type="number" name="capacidad_maxima" class="form-control" value="{{ $est->capacidad_maxima }}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label text-muted fw-semibold small">Biodigestor (L)</label>
                                                                        <input type="number" name="capacidad_biodigestor" class="form-control" value="{{ $est->capacidad_biodigestor }}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label text-muted fw-semibold small">Destino Efluente</label>
                                                                        <select name="tipo_destino_vuelco" class="form-select">
                                                                            <option value="cursos_agua" {{ $est->tipo_destino_vuelco == 'cursos_agua' ? 'selected' : '' }}>Cursos de agua</option>
                                                                            <option value="laguna" {{ $est->tipo_destino_vuelco == 'laguna' ? 'selected' : '' }}>Laguna</option>
                                                                            <option value="conducto_pluvial" {{ $est->tipo_destino_vuelco == 'conducto_pluvial' ? 'selected' : '' }}>Conducto Pluvial</option>
                                                                            <option value="absorcion_suelo" {{ $est->tipo_destino_vuelco == 'absorcion_suelo' ? 'selected' : '' }}>Absorción en suelo / Pozo</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light" style="border-radius: 0 0 16px 16px;">
                                                                <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4">Guardar Cambios</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- FIN COLUMNA IZQUIERDA -->

                <!-- COLUMNA DERECHA (BANNER LATERAL) -->
                <div class="col-lg-4">
                    <div class="card card-custom overflow-hidden text-white h-100 position-relative" style="background: url('https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&w=600&q=80') center/cover; min-height: 250px;">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(10,37,18, 0.75);"></div>
                        <div class="position-relative p-4 d-flex flex-column justify-content-between h-100">
                            <div>
                                <span class="badge bg-success mb-2">Comunidad</span>
                                <h4 class="fw-bold">Cuidemos juntos el Iberá</h4>
                                <p class="small text-light">El agua y el medioambiente también son parte de nuestro futuro. Vigilamos que cada establecimiento cumpla con la ley.</p>
                            </div>
                            <div>
                                <a href="#" class="btn btn-light btn-sm fw-bold px-3 py-2 text-success rounded-pill">Ver detalles <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div> <!-- FIN COLUMNA DERECHA -->

            </div>
        </div>
    </div>
</div>

<!-- VENTANA FLOTANTE (MODAL) PARA CREAR ESTABLECIMIENTO -->
<div class="modal fade" id="modalNuevoEstablecimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header bg-success text-white" style="border-radius: 16px 16px 0 0;">
                <h1 class="modal-title fs-5 fw-bold"><i class="bi bi-building me-2"></i>Cargar Nuevo Establecimiento</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('icaa.establecimientos.guardar') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold small">Nombre del establecimiento</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej: El Café de los Pájaros">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold small">CUIT</label>
                            <input type="number" name="cuit" class="form-control" required placeholder="Sin guiones">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold small">Ubicación (Localidad)</label>
                            <input type="text" name="ubicacion" class="form-control" required value="Colonia Carlos Pellegrini">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold small">Rubro</label>
                            <select name="rubro" class="form-select" required>
                                <option value="gastronomico">Gastronomía</option>
                                <option value="hotel">Hotel</option>
                                <option value="comercio">Comercio</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted fw-semibold small">Cap. Máxima (personas)</label>
                            <input type="number" name="capacidad_maxima" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted fw-semibold small">Cap. Biodigestor (L)</label>
                            <input type="number" name="capacidad_biodigestor" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted fw-semibold small">Destino del Efluente</label>
                            <select name="tipo_destino_vuelco" class="form-select">
                                <option value="cursos_agua">Cursos de agua</option>
                                <option value="laguna">Laguna</option>
                                <option value="conducto_pluvial">Conducto Pluvial</option>
                                <option value="absorcion_suelo">Absorción en suelo / Pozo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill fw-bold px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>