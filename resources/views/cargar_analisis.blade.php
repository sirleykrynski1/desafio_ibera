@extends('layouts.app')

@section('content')
<div class="row mt-4">
    <div class="col-md-8 mx-auto">
        <h2 class="mb-4">Cargar Nuevo Análisis de Laboratorio</h2>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <!-- La acción apuntará a la ruta API que armará la Persona 2 -->
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="pdf" class="form-label fw-bold">Subir archivo PDF del laboratorio</label>
                        <input class="form-control" type="file" id="pdf" name="pdf" accept=".pdf">
                    </div>

                    <hr class="my-4">

                    <!-- Switch para mostrar/ocultar carga manual -->
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="toggleManual">
                        <label class="form-check-label fw-bold text-muted" for="toggleManual">Si no tienes PDF, ingresa los valores manualmente</label>
                    </div>

                    <!-- Campos de carga manual (ocultos por defecto) -->
                    <div id="manualInputs" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" class="form-control" name="temperatura">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">pH</label>
                                <input type="number" step="0.1" class="form-control" name="ph">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sólidos Suspendidos (mg/l)</label>
                                <input type="number" step="0.1" class="form-control" name="solidos">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DBO5 (mg/l)</label>
                                <input type="number" step="0.1" class="form-control" name="dbo5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DQO (mg/l)</label>
                                <input type="number" step="0.1" class="form-control" name="dqo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Detergentes (mg/l)</label>
                                <input type="number" step="0.1" class="form-control" name="detergentes">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hidrocarburos (mg/l)</label>
                                <input type="number" step="0.1" class="form-control" name="hidrocarburos">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4">Evaluar Cumplimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para el toggle de carga manual -->
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