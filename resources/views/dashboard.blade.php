@extends('layouts.app')

@section('content')
<div class="container-fluid py-2">
    <!-- Encabezado con saludo -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">¡Hola, Hotel Paraíso!</h2>
        <p class="text-muted small">Acá podés ver el estado de tus análisis y el cumplimiento de la normativa ambiental.</p>
    </div>

    <!-- Malla Superior: Métricas principales (3 Columnas) -->
    <div class="row g-3 mb-4">
        <!-- 1. Semáforo / Estado de Cumplimiento -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-check-circle-fill display-6"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Estado de cumplimiento</small>
                        <h3 class="fw-bold text-dark mb-0">85%</h3>
                        <small class="text-success fw-bold">de los análisis en norma</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Vencimiento del Permiso -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                <small class="text-muted d-block fw-semibold">Vencimiento del permiso</small>
                <h4 class="fw-bold text-dark mt-2 mb-1">15 abr 2026</h4>
                <div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">En vigencia</span>
                </div>
            </div>
        </div>

        <!-- 3. Alertas Climáticas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-warning-subtle border-warning-subtle">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    <strong class="small text-warning-emphasis">Alertas climáticas</strong>
                </div>
                <h6 class="fw-bold text-dark mb-1">Lluvias intensas próximas</h6>
                <small class="text-muted">Puede haber saturación de pozos absorbentes.</small>
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

    <!-- Malla Inferior: Últimos Análisis + Banner promocional Iberá -->
    <div class="row g-3">
        <!-- Tabla de Últimos Análisis (Columna Izquierda) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">Últimos análisis</h5>
                    <a href="{{ route('analisis.index') }}" class="text-success text-decoration-none small fw-bold">Ver historial completo →</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th>Fecha</th>
                                <th>Resultado</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr>
                                <td>
                                    <strong>10 abr 2025</strong><br>
                                    <span class="text-muted small">Análisis #0156</span>
                                </td>
                                <td class="fw-bold text-success">Cumple</td>
                                <td><span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill">En norma</span></td>
                                <td class="text-end"><a href="#" class="btn btn-sm btn-light border text-secondary"><i class="bi bi-eye me-1"></i>Ver detalle</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>25 mar 2025</strong><br>
                                    <span class="text-muted small">Análisis #0155</span>
                                </td>
                                <td class="fw-bold text-warning">Alerta</td>
                                <td><span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1 rounded-pill">En alerta</span></td>
                                <td class="text-end"><a href="#" class="btn btn-sm btn-light border text-secondary"><i class="bi bi-eye me-1"></i>Ver detalle</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>12 mar 2025</strong><br>
                                    <span class="text-muted small">Análisis #0154</span>
                                </td>
                                <td class="fw-bold text-success">Cumple</td>
                                <td><span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill">En norma</span></td>
                                <td class="text-end"><a href="#" class="btn btn-sm btn-light border text-secondary"><i class="bi bi-eye me-1"></i>Ver detalle</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>28 feb 2025</strong><br>
                                    <span class="text-muted small">Análisis #0153</span>
                                </td>
                                <td class="fw-bold text-danger">Incumple</td>
                                <td><span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill">Fuera de norma</span></td>
                                <td class="text-end"><a href="#" class="btn btn-sm btn-light border text-secondary"><i class="bi bi-eye me-1"></i>Ver detalle</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Banner Cuidemos juntos el Iberá (Columna Derecha) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden text-white h-100 position-relative bg-dark" style="min-height: 250px;">
                <!-- Fondo verde oscuro decorativo -->
                <div class="position-absolute w-100 h-100" style="background: linear-gradient(180deg, rgba(16, 85, 47, 0.85) 0%, rgba(5, 38, 20, 0.95) 100%);"></div>
                
                <div class="card-body p-4 position-relative d-flex flex-column justify-content-end z-1">
                    <h4 class="fw-bold mb-2">Cuidemos juntos el Iberá</h4>
                    <p class="small text-white-50 mb-3">El agua también es parte de nuestro futuro.</p>
                    <div>
                        <a href="{{ route('insignias.index') }}" class="btn btn-light text-success font-semibold btn-sm px-3 rounded-2">
                            <i class="bi bi-check-circle me-1"></i> Ver mis insignias
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
