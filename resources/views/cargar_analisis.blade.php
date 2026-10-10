@extends('layouts.app')

@section('content')
<div class="row mt-4">
    <div class="col-md-9 mx-auto">
        <div class="d-flex justify-content-between items-center mb-3">
            <div>
                <h2 class="fw-bold mb-1">Cargar análisis de laboratorio</h2>
                <p class="text-muted small mb-0">Podés subir un PDF del laboratorio o ingresar los valores manualmente.</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <!-- La acción apuntará a la ruta que arme la Persona 2 -->
                <form action="{{ route('analisis.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Campo Nombre del Laboratorio -->
                    <div class="mb-4">
                        <label for="laboratorio" class="form-label fw-bold text-dark">Laboratorio que realizó el análisis</label>
                        <input type="text" class="form-control" id="laboratorio" name="laboratorio" placeholder="Ej: Laboratorio Central Corrientes">
                    </div>

                    <!-- Subida de PDF -->
                    <div id="pdfSection" class="mb-4">
                        <label for="pdf" class="form-label fw-bold text-dark">Subir archivo PDF del laboratorio</label>
                        <div class="border border-2 border-dashed rounded-3 p-4 text-center bg-light">
                            <i class="bi bi-file-earmark-pdf text-muted display-6 mb-2"></i>
                            <input class="form-control" type="file" id="pdf" name="pdf" accept=".pdf">
                            <div class="form-text mt-2">Formatos válidos: PDF (máx. 10 MB)</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Switch para mostrar/ocultar carga manual -->
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="toggleManual" name="es_manual">
                        <label class="form-check-label fw-bold text-secondary" for="toggleManual">
                            No tengo PDF, ingreso valores manualmente (confirmado)
                        </label>
                    </div>

                    <!-- Campos de carga manual (7 Parámetros Res. 312/21) -->
                    <div id="manualInputs" style="display: none;" class="bg-light p-3 rounded-3 border mb-4">
                        <h6 class="fw-bold text-dark mb-3">Completar datos del análisis</h6>
                        
                        <div class="row g-3">
                            <!-- 1. Temperatura -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Temperatura (°C)</label>
                                <span class="text-muted small float-end">Límite: ≤ 45 °C</span>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="temperatura" placeholder="Ej: 38">
                            </div>

                            <!-- 2. pH -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">pH</label>
                                <span class="text-muted small float-end">Límite: 5,5 - 10</span>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="ph" placeholder="Ej: 7.1">
                            </div>

                            <!-- 3. Sólidos Suspendidos -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Sólidos Suspendidos (mg/l)</label>
                                <span class="text-muted small float-end">Límite: ≤ 35 mg/l</span>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="solidos_suspendidos" placeholder="Ej: 28">
                            </div>

                            <!-- 4. DBO5 -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">DBO5 (mg/l)</label>
                                <span class="text-muted small float-end">Límite: ≤ 50 mg/l</span>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="dbo5" placeholder="Ej: 42">
                            </div>

                            <!-- 5. DQO -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">DQO (mg/l)</label>
                                <span class="text-muted small float-end">Límite: ≤ 250 mg/l</span>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="dqo" placeholder="Ej: 180">
                            </div>

                            <!-- 6. Detergentes -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Detergentes (mg/l)</label>
                                <span class="text-muted small float-end">Límite: ≤ 2 mg/l</span>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="detergentes" placeholder="Ej: 1.2">
                            </div>

                            <!-- 7. Hidrocarburos -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Hidrocarburos (mg/l)</label>
                                <span class="text-muted small float-end">Límite: ≤ 30 mg/l</span>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="hidrocarburos" placeholder="Ej: 15">
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 bg-emerald">Guardar análisis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script de interacción cliente -->
<script>
    document.getElementById('toggleManual').addEventListener('change', function() {
        var manualSection = document.getElementById('manualInputs');
        if(this.checked) {
            manualSection.style.display = 'block';
        } else {
            manualSection.style.display = 'none';
        }
    });
</script>
@endsection
