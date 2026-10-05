@extends('layouts.app')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Historial de Análisis</h2>
            
            <!-- Filtro con ID para que JavaScript lo detecte -->
            <select id="filtroEstado" class="form-select w-auto">
                <option value="todos">Todos los estados</option>
                <option value="cumple">Cumple Normativa</option>
                <option value="incumple">Incumple</option>
            </select>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Resumen de Parámetros</th>
                            <th>Resultado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila 1: Le agregamos la clase 'fila-analisis' y el data-estado='cumple' -->
                        <tr class="fila-analisis" data-estado="cumple">
                            <td>15/09/2026</td>
                            <td><small class="text-muted">Temp: 38°C | pH: 7.1 | DBO5: 42 mg/l...</small></td>
                            <td><span class="badge bg-success px-3 py-2 rounded-pill">Cumple</span></td>
                            <td><button class="btn btn-sm btn-outline-secondary">Descargar PDF</button></td>
                        </tr>
                        
                        <!-- Fila 2: Le agregamos la clase 'fila-analisis' y el data-estado='incumple' -->
                        <tr class="fila-analisis" data-estado="incumple">
                            <td>10/08/2026</td>
                            <td><small class="text-muted">Temp: 46°C | pH: 5.0 | DBO5: 60 mg/l...</small></td>
                            <td><span class="badge bg-danger px-3 py-2 rounded-pill">Incumple</span></td>
                            <td><button class="btn btn-sm btn-outline-secondary">Descargar PDF</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script nativo para simular el filtro -->
<script>
    document.getElementById('filtroEstado').addEventListener('change', function() {
        let estadoSeleccionado = this.value;
        let filas = document.querySelectorAll('.fila-analisis');

        filas.forEach(fila => {
            // Si elige "todos" o el estado de la fila coincide con el select, se muestra. Si no, se oculta.
            if (estadoSeleccionado === 'todos' || fila.getAttribute('data-estado') === estadoSeleccionado) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
</script>
@endsection