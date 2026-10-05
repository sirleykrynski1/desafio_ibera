@extends('layouts.app')

@section('title', 'Mis Establecimientos - Gestión Ambiental')

@section('content')
<div class="row g-4">
    <!-- Encabezado con botón de acción principal -->
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-2 border-bottom border-success-subtle">
            <div>
                <h1 class="h3 text-success fw-bold mb-1">
                    Mis Establecimientos 🦦
                </h1>
                <p class="text-muted mb-0">
                    Administra tus hospedajes, capacidades de fosa séptica y control de vertidos en el humedal.
                </p>
            </div>
            <div>
                <a href="#" class="btn btn-eco shadow-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <span>➕</span> Añadir Nuevo Establecimiento
                </a>
            </div>
        </div>
    </div>

    <!-- Tarjeta con la tabla de establecimientos -->
    <div class="col-12">
        <div class="card card-eco shadow-sm">
            <div class="card-header card-eco-header d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold fs-5 d-flex align-items-center gap-2">
                    <span>🌿</span> Listado de Alojamientos y Complejos Turísticos
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                    3 Registrados
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light border-bottom border-success-subtle">
                            <tr class="text-secondary small text-uppercase">
                                <th class="ps-4">Establecimiento</th>
                                <th>Ubicación / Portal</th>
                                <th>Capacidad Fosa</th>
                                <th>Nivel Estimado</th>
                                <th>Última Limpieza</th>
                                <th>Estado Ambiental</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Fila de prueba 1 -->
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success-subtle p-2 rounded-circle text-success fw-bold">
                                            🦦
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Eco-Lodge El Yacaré Dorado</div>
                                            <small class="text-muted">ID: EST-001 &bull; 18 Huéspedes máx.</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary">Portal Carambola, Concepción</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">5.000 L</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px; width: 110px;" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-success" style="width: 35%"></div>
                                    </div>
                                    <small class="text-muted">35% ocupado</small>
                                </td>
                                <td>
                                    <span class="text-secondary">15/09/2026</span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        ✅ En Regla
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" title="Registrar Mantenimiento">
                                            🐸 Limpiar
                                        </button>
                                        <button class="btn btn-outline-secondary" title="Ver Detalles">
                                            👁️
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Fila de prueba 2 -->
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success-subtle p-2 rounded-circle text-success fw-bold">
                                            🦩
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Posada de las Garzas Rosadas</div>
                                            <small class="text-muted">ID: EST-002 &bull; 30 Huéspedes máx.</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary">Portal Laguna Iberá, Pellegrini</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">8.000 L</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px; width: 110px;" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-warning" style="width: 82%"></div>
                                    </div>
                                    <small class="text-warning-emphasis fw-semibold">82% (Atención)</small>
                                </td>
                                <td>
                                    <span class="text-secondary">02/08/2026</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        ⚠️ Próximo a Vencer
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-warning text-dark fw-semibold" title="Registrar Mantenimiento">
                                            🐸 Limpiar
                                        </button>
                                        <button class="btn btn-outline-secondary" title="Ver Detalles">
                                            👁️
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Fila de prueba 3 -->
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success-subtle p-2 rounded-circle text-success fw-bold">
                                            🌿
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Cabañas del Humedal Esteros</div>
                                            <small class="text-muted">ID: EST-003 &bull; 12 Huéspedes máx.</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary">Portal San Nicolás, San Miguel</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">3.500 L</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px; width: 110px;" role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-success" style="width: 15%"></div>
                                    </div>
                                    <small class="text-muted">15% ocupado</small>
                                </td>
                                <td>
                                    <span class="text-secondary">28/09/2026</span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        ✅ En Regla
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" title="Registrar Mantenimiento">
                                            🐸 Limpiar
                                        </button>
                                        <button class="btn btn-outline-secondary" title="Ver Detalles">
                                            👁️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">Mostrando 3 de 3 establecimientos</small>
                <a href="#" class="text-success text-decoration-none fw-semibold small">
                    📥 Descargar Reporte Ambiental (PDF)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
