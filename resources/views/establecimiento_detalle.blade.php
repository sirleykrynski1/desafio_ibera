@extends('layouts.app')
@section('content')
<h1 class="h3">{{ $establecimiento->nombre }}</h1>
<p>{{ $establecimiento->ubicacion }} · Destino: {{ $establecimiento->tipo_destino_vuelco }}</p>
@can('update', $establecimiento)
<a class="btn btn-outline-success mb-3" href="{{ route('establecimientos.editar', $establecimiento) }}">Editar</a>
<a class="btn btn-success mb-3" href="{{ route('analisis.crear') }}">Cargar análisis</a>
@endcan
<h2 class="h4">Permiso de vuelco</h2>
@if($establecimiento->permisoVuelco)
<p>Expediente: {{ $establecimiento->permisoVuelco->numero_expediente }} · Estado: {{ $establecimiento->permisoVuelco->estado }} · Trámite: {{ $establecimiento->permisoVuelco->estado_tramite }}</p>
@else
<p>No hay un permiso registrado.</p>
@endif
@can('gestionarPermiso', $establecimiento)
<form action="{{ route('permisos.guardar', $establecimiento) }}" method="POST" class="card card-body">
@csrf
@foreach(['numero_expediente' => 'Expediente', 'fecha_emision' => 'Fecha de emisión', 'fecha_vencimiento' => 'Fecha de vencimiento'] as $campo => $texto)
<label for="{{ $campo }}">{{ $texto }}</label><input id="{{ $campo }}" name="{{ $campo }}" class="form-control mb-3" type="{{ str_starts_with($campo, 'fecha') ? 'date' : 'text' }}" value="{{ old($campo, str_starts_with($campo, 'fecha') ? $establecimiento->permisoVuelco?->$campo?->format('Y-m-d') : $establecimiento->permisoVuelco?->$campo) }}" required>
@endforeach
@foreach(['estado' => ['Activo', 'Vencido', 'Revocado'], 'estado_tramite' => ['iniciado', 'pendiente_documentacion', 'en_evaluacion', 'resuelto'], 'tipo_destino_vuelco' => ['cursos_agua', 'laguna', 'conducto_pluvial', 'absorcion_suelo']] as $campo => $opciones)
<label for="{{ $campo }}">{{ str_replace('_', ' ', ucfirst($campo)) }}</label>
<select id="{{ $campo }}" name="{{ $campo }}" class="form-select mb-3">
@foreach($opciones as $opcion)<option value="{{ $opcion }}" @selected(old($campo, $establecimiento->permisoVuelco?->$campo) === $opcion)>{{ $opcion }}</option>@endforeach
</select>
@endforeach
<button class="btn btn-success">Guardar permiso</button>
</form>
@endcan
@endsection
