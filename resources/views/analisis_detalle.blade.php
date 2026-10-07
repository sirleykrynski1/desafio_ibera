@extends('layouts.app')
@section('content')
<h1 class="h3">Análisis #{{ $analisis->getKey() }}</h1>
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
@endsection
