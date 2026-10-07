@extends('layouts.app')
@section('content')
<h1 class="h3">{{ $establecimiento->exists ? 'Editar' : 'Registrar' }} establecimiento</h1>
<form method="POST" action="{{ $establecimiento->exists ? route('establecimientos.actualizar', $establecimiento) : route('establecimientos.guardar') }}" class="card card-body">
@csrf
@if($establecimiento->exists) @method('PUT') @endif
@foreach(['nombre' => 'Nombre', 'ubicacion' => 'Ubicación', 'cuit' => 'CUIT (opcional, 11 dígitos)', 'latitud' => 'Latitud', 'longitud' => 'Longitud', 'capacidad_maxima' => 'Capacidad máxima de personas', 'capacidad_biodigestor' => 'Capacidad del biodigestor (litros)'] as $campo => $etiqueta)
<label for="{{ $campo }}">{{ $etiqueta }}</label>
<input class="form-control mb-3" id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo, $establecimiento->$campo) }}" @required($campo !== 'cuit')>
@endforeach
<label for="rubro">Rubro</label>
<select id="rubro" name="rubro" class="form-select mb-3">
@foreach(['hotel' => 'Hotel', 'gastronomico' => 'Gastronómico', 'comercio' => 'Comercio'] as $valor => $texto)
<option value="{{ $valor }}" @selected(old('rubro', $establecimiento->rubro) === $valor)>{{ $texto }}</option>
@endforeach
</select>
<label for="tipo_destino_vuelco">Destino del efluente</label>
<select id="tipo_destino_vuelco" name="tipo_destino_vuelco" class="form-select mb-3">
@foreach(['cursos_agua' => 'Curso de agua', 'laguna' => 'Laguna', 'conducto_pluvial' => 'Conducto pluvial', 'absorcion_suelo' => 'Absorción en suelo'] as $valor => $texto)
<option value="{{ $valor }}" @selected(old('tipo_destino_vuelco', $establecimiento->tipo_destino_vuelco) === $valor)>{{ $texto }}</option>
@endforeach
</select>
<button class="btn btn-success">Guardar establecimiento</button>
</form>
@endsection
