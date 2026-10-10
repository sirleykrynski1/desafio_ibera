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
                <form action="{{ route('analisis.guardar') }}" method="POST" enctype="multipart/form-data" class="card card-body">
    @csrf
    <label for="establecimiento_id">Establecimiento</label>
    <select id="establecimiento_id" name="establecimiento_id" class="form-select mb-3" required>
        @foreach($establecimientos as $establecimiento)
        <option value="{{ $establecimiento->id }}" @selected(old('establecimiento_id') == $establecimiento->id)>{{ $establecimiento->nombre }}</option>
        @endforeach
    </select>
    <label for="pdf">Informe PDF (máximo 10 MB)</label>
    <input class="form-control mb-3" type="file" id="pdf" name="pdf" accept=".pdf,application/pdf" required>
    <p>Si no podemos leer el informe, quedará registrado para revisión. El resultado automático no reemplaza el dictamen del inspector.</p>
    <button class="btn btn-success">Registrar análisis</button>
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
