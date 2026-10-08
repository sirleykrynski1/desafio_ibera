@extends('layouts.app')
@section('content')
<h1 class="h3">Panel de gestión</h1>
<p>Análisis pendientes de revisión, comenzando por los más antiguos. Consultá el clima del establecimiento para ayudar a priorizar inspecciones.</p>
<a href="{{ route('establecimientos.indice') }}">Consultar establecimientos y permisos</a>
<div class="table-responsive"><table class="table">
<thead><tr><th>Establecimiento</th><th>Lectura</th><th>Evaluación automática</th><th>Acciones</th></tr></thead>
<tbody>
@forelse($analisis as $informe)
<tr><td>{{ $informe->establecimiento->nombre }}</td><td>{{ $informe->estado }}</td><td>{{ $informe->resultado_sugerido ?? 'Sin evaluación' }}</td>
<td><a href="{{ route('analisis.mostrar', $informe) }}">Revisar análisis</a> · <a href="{{ route('establecimientos.clima', $informe->establecimiento) }}">Consultar clima</a></td></tr>
@empty
<tr><td colspan="4">No hay análisis pendientes.</td></tr>
@endforelse
</tbody></table></div>
{{ $analisis->links() }}
@endsection
