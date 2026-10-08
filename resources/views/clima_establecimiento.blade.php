@extends('layouts.app')
@section('content')
<h1 class="h3">Clima de {{ $establecimiento->nombre }}</h1>
<p>Esta información ayuda a priorizar inspecciones. No determina el cumplimiento de los efluentes ni modifica el dictamen del laboratorio.</p>
@if(!$coordenadasValidas)
<div class="alert alert-warning">Faltan coordenadas válidas. El propietario debe completarlas en el establecimiento.</div>
@elseif($alerta === null)
<div class="alert alert-warning">Pronóstico no disponible. No se puede determinar si hay alerta climática. Intentá nuevamente más tarde.</div>
@else
<div class="alert {{ $alerta['alerta']['activa'] ? 'alert-warning' : 'alert-info' }}">
<strong>{{ $alerta['alerta']['activa'] ? 'Alerta por precipitaciones' : 'Sin alerta de precipitaciones para el período consultado' }}</strong>
<p>Lluvia acumulada prevista: {{ $alerta['precipitacion_acumulada_mm'] }} mm.</p>
<p>Período: {{ $alerta['periodo']['desde'] }} a {{ $alerta['periodo']['hasta'] }} ({{ $alerta['periodo']['zona_horaria'] }}).</p>
<ul>@foreach($alerta['alerta']['motivos'] as $motivo)<li>{{ $motivo }}</li>@endforeach</ul>
</div>
<p>Fuente: {{ $alerta['fuente'] }}. Consulta: {{ $alerta['consultado_en'] }}.</p>
<p>Reglas climáticas: {{ $alerta['version_reglas'] }}. Criterio provisional del prototipo, pendiente de validación con especialistas.</p>
@endif
<a class="btn btn-outline-success" href="{{ route('establecimientos.clima', $establecimiento) }}">Volver a consultar</a>
<a href="{{ route('establecimientos.mostrar', $establecimiento) }}">Volver al establecimiento</a>
@endsection
