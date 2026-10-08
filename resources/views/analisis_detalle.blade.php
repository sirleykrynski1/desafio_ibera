@extends('layouts.app')
@section('content')
<h1 class="h3">Análisis #{{ $analisis->getKey() }}</h1>
@if($analisis->ruta_pdf)<p><a href="{{ route('analisis.pdf', $analisis) }}">Descargar PDF original</a></p>@endif
<p>Establecimiento: {{ $analisis->establecimiento->nombre }}</p>
<p>Laboratorio: {{ $analisis->laboratorio }} · Fecha: {{ $analisis->fecha_muestra->format('d/m/Y') }}</p>
<p>Estado de lectura: <strong>{{ $analisis->estado }}</strong></p>
<p>Resultado automático: <strong>{{ $analisis->resultado_sugerido ?? 'Sin evaluación' }}</strong></p>
<p>Dictamen final: <strong>{{ $analisis->resultado_final }}</strong></p>
@if($analisis->estado === 'observado')
<div class="alert alert-warning">Este informe necesita revisión humana.</div>
@endif
<div class="table-responsive"><table class="table"><thead><tr><th>Parámetro</th><th>Valor</th><th>Unidad</th></tr></thead><tbody>
@forelse($analisis->parametros as $parametro)
<tr><td>{{ $parametro->nombre }}</td><td>{{ $parametro->valor_medido }}</td><td>{{ $parametro->unidad }}</td></tr>
@empty
<tr><td colspan="3">No se pudieron extraer mediciones.</td></tr>
@endforelse
</tbody></table></div>
@if($analisis->revisado_en)
<h2 class="h4">Revisión humana</h2>
<p>Revisado por {{ $analisis->revisor?->name ?? 'Usuario no disponible' }} el {{ $analisis->revisado_en->format('d/m/Y H:i') }} ({{ config('app.timezone') }}).</p>
<p>{{ $analisis->observaciones_revision }}</p>
@endif
@can('revisarAnalisis', $analisis->establecimiento)
@if($analisis->resultado_final === 'Pendiente' && in_array($analisis->estado, ['evaluado', 'observado'], true))
<form method="POST" action="{{ route('analisis.revision', $analisis) }}" class="card card-body">
@csrf
<h2 class="h4">Registrar dictamen</h2>
<p>Revisá la documentación antes de decidir. La decisión no reemplaza la evaluación automática. Una vez registrada, no se puede sobrescribir desde esta pantalla.</p>
<label for="resultado_final">Decisión</label>
<select class="form-select mb-3" id="resultado_final" name="resultado_final" required>
<option value="">Seleccioná una decisión</option>
@foreach(['Aprobado', 'Rechazado'] as $decision)<option value="{{ $decision }}" @selected(old('resultado_final') === $decision)>{{ $decision }}</option>@endforeach
</select>
<label for="observaciones_revision">Fundamento de la revisión</label>
<textarea class="form-control mb-3" id="observaciones_revision" name="observaciones_revision" maxlength="2000" required>{{ old('observaciones_revision') }}</textarea>
<button class="btn btn-success">Registrar dictamen final</button>
</form>
@endif
<p><a href="{{ route('gestion') }}">Volver a pendientes</a></p>
@endcan
@endsection
