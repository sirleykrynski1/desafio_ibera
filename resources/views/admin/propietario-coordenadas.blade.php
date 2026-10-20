@php
    $formulario = $establecimientoFormulario?->id ?? 'nuevo';
    $recuperar = (string) old('_formulario') === (string) $formulario;
    $propietarioSeleccionado = $recuperar ? old('user_id') : $establecimientoFormulario?->user_id;
@endphp
<input type="hidden" name="_formulario" value="{{ $formulario }}">
<div class="mb-3">
    <label class="form-label" for="propietario-{{ $formulario }}">Propietario</label>
    <select id="propietario-{{ $formulario }}" name="user_id" class="form-select" required>
        <option value="">Seleccioná una cuenta de propietario</option>
        @foreach($propietarios as $propietario)
            <option value="{{ $propietario->id }}" @selected((string) $propietarioSeleccionado === (string) $propietario->id)>{{ $propietario->nombre }} {{ $propietario->apellido }} — {{ $propietario->email }}</option>
        @endforeach
    </select>
    <small>Si no aparece, primero debe registrarse como propietario.</small>
</div>
<div class="mb-3">
    <label class="form-label" for="latitud-{{ $formulario }}">Latitud</label>
    <input id="latitud-{{ $formulario }}" type="number" name="latitud" step="any" min="-90" max="90" class="form-control" required value="{{ $recuperar ? old('latitud') : $establecimientoFormulario?->latitud }}">
</div>
<div class="mb-3">
    <label class="form-label" for="longitud-{{ $formulario }}">Longitud</label>
    <input id="longitud-{{ $formulario }}" type="number" name="longitud" step="any" min="-180" max="180" class="form-control" required value="{{ $recuperar ? old('longitud') : $establecimientoFormulario?->longitud }}">
    <small>Ingresá las coordenadas reales para consultar el pronóstico de su ubicación.</small>
</div>
