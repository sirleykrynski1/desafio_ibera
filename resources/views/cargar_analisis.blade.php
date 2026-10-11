@extends('layouts.app')
@section('content')
<h1 class="h3 mb-4">Cargar análisis de laboratorio</h1>
@if($establecimientos->isEmpty())
    <p>Primero registrá tu establecimiento.</p>
    <a href="{{ route('establecimientos.crear') }}" class="btn btn-success">Registrar establecimiento</a>
@else
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
@endif
@endsection
