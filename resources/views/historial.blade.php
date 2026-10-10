@extends('layouts.app')

@section('content')
<div class="container-fluid py-2">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Historial de análisis</h2>
            <p class="text-muted small mb-0">Revisá todos los análisis realizados y filtrá por fecha o estado.</p>
        </div>
        <a href="{{ route('analisis.create') }}" class="btn btn-success font-semibold px-3"><i class="bi bi-plus-lg me-1"></i> Cargar análisis</a>
    </div>

    <!-- Barra de Filtros (Fecha Desde/Hasta + Estado) -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold mb-1">Desde</label>
                <input type="date" class="form-control form-control-sm" id="fechaDesde">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold mb-1">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="fechaHasta">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted fw-semibold mb-1">Estado</label>
                <select id="filtroEstado" class="form-select form-select-sm">
                    <option value="todos">Todos los estados</option>
                    <option value="cumple">Cumple (En norma)</option>
                    <option value="alerta">Alerta (En alerta)</option>
                    <option value="incumple">Incumple (Fuera de norma)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-emerald btn-success btn-sm w-100 fw-semibold">Filtrar</button>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Historial -->
    <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th>Fecha</th>
                        <th>N° Análisis</th>
                        <th>Resultado</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <!-- Fila 1: Cumple -->
                    <tr class="fila-analisis" data-estado="cumple">
                        <td>10 abr 2025</td>
                        <td class="fw-bold text-success">#0156</td>
                        <td class="fw-semibold text-success">Cumple</td>
                        <td><span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill">En norma</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border text-secondary me-1"><i class="bi bi-eye me-1"></i>Ver detalle</button>
                            <button class="btn btn-sm btn-light border text-secondary"><i class="bi bi-download"></i></button>
                        </td>
                    </tr>

                    <!-- Fila 2: Alerta -->
                    <tr class="fila-analisis" data-estado="alerta">
                        <td>25 mar 2025</td>
                        <td class="fw-bold text-success">#0155</td>
                        <td class="fw-semibold text-warning">Alerta</td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1.5 rounded-pill">En alerta</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border text-secondary me-1"><i class="bi bi-eye me-1"></i>Ver detalle</button>
                            <button class="btn btn-sm btn-light border text-secondary"><i class="bi bi-download"></i></button>
                        </td>
                    </tr>

                    <!-- Fila 3: Cumple -->
                    <tr class="fila-analisis" data-estado="cumple">
                        <td>12 mar 2025</td>
                        <td class="fw-bold text-success">#0154</td>
                        <td class="fw-semibold text-success">Cumple</td>
                        <td><span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill">En norma</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border text-secondary me-1"><i class="bi bi-eye me-1"></i>Ver detalle</button>
                            <button class="btn btn-sm btn-light border text-secondary"><i class="bi bi-download"></i></button>
                        </td>
                    </tr>

                    <!-- Fila 4: Incumple -->
                    <tr class="fila-analisis" data-estado="incumple">
                        <td>28 feb 2025</td>
                        <td class="fw-bold text-success">#0153</td>
                        <td class="fw-semibold text-danger">Incumple</td>
                        <td><span class="badge bg-danger-subtle text-danger px-3 py-1.5 rounded-pill">Fuera de norma</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border text-secondary me-1"><i class="bi bi-eye me-1"></i>Ver detalle</button>
                            <button class="btn btn-sm btn-light border text-secondary"><i class="bi bi-download"></i></button>
                        </td>
                    </tr>

                    <!-- Fila 5: Cumple -->
                    <tr class="fila-analisis" data-estado="cumple">
                        <td>15 feb 2025</td>
                        <td class="fw-bold text-success">#0152</td>
                        <td class="fw-semibold text-success">Cumple</td>
                        <td><span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill">En norma</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border text-secondary me-1"><i class="bi bi-eye me-1"></i>Ver detalle</button>
                            <button class="btn btn-sm btn-light border text-secondary"><i class="bi bi-download"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Tabla: Paginación + Contador -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top small text-muted">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">&lt;</a></li>
                <li class="page-item active"><a class="page-link bg-success border-success" href="#">1</a></li>
                <li class="page-item"><a class="page-link text-dark" href="#">2</a></li>
                <li class="page-item"><a class="page-link text-dark" href="#">3</a></li>
                <li class="page-item"><a class="page-link text-dark" href="#">&gt;</a></li>
            </ul>
            <span>Mostrando 1-5 de 18</span>
        </div>
    </div>
</div>

<!-- Script del filtro JS -->
<script>
    document.getElementById('filtroEstado').addEventListener('change', function() {
        let estadoSeleccionado = this.value;
        let filas = document.querySelectorAll('.fila-analisis');

        filas.forEach(fila => {
            if (estadoSeleccionado === 'todos' || fila.getAttribute('data-estado') === estadoSeleccionado) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
</script>
@endsection
