@extends('layouts.app')
@section('content')
<h1 class="h3">Historial de alertas climáticas</h1>
<p>Detecciones guardadas por el proceso climático. Una alerta pendiente de revisión no significa que el pronóstico siga vigente. Consultá el clima actual antes de priorizar una visita.</p>
<p>No tener alertas guardadas no garantiza ausencia de riesgo: la API o el proceso programado pueden no estar disponibles.</p>
<form method="GET" action="{{ route('alertas.indice') }}" class="mb-3">
<label for="estado">Mostrar</label>
<select name="estado" id="estado" class="form-select">
@foreach(['pendientes' => 'Pendientes', 'revisadas' => 'Revisadas', 'todas' => 'Todas'] as $valor => $etiqueta)
<option value="{{ $valor }}" @selected($estado === $valor)>{{ $etiqueta }}</option>
@endforeach
</select><button class="btn btn-outline-success mt-2">Filtrar</button>
</form>
@forelse($alertas as $alerta)
<article class="card card-body mb-3">
<h2 class="h5">{{ $alerta->establecimiento->nombre }}</h2>
<p>{{ $alerta->tipo }} · {{ $alerta->milimetros_lluvia ?? 'Sin dato' }} mm acumulados.</p>
<p>Período: {{ $alerta->fecha_evento->format('d/m/Y') }} a {{ $alerta->periodo_hasta?->format('d/m/Y') ?? 'No registrado' }}.</p>
<p>Consulta: {{ $alerta->consultado_en ? $alerta->consultado_en->utc()->format('d/m/Y H:i').' UTC' : 'No registrada' }}.</p>
<ul>@foreach(($alerta->detalle['alerta']['motivos'] ?? []) as $motivo)<li>{{ $motivo }}</li>@endforeach</ul>
<a href="{{ route('establecimientos.clima', $alerta->establecimiento) }}">Consultar clima actual</a>
@if($alerta->revisada_en)
<p>Revisada: {{ $alerta->revisada_en->format('d/m/Y H:i') }} ({{ config('app.timezone') }}).</p>
@else
<form method="POST" action="{{ route('alertas.revisar', $alerta) }}" class="mt-2">@csrf @method('PATCH')
<button class="btn btn-success">Marcar como revisada</button></form>
@endif
</article>
@empty
<p>No hay alertas para este filtro.</p>
@endforelse
{{ $alertas->links() }}
<a href="{{ route('gestion') }}">Volver a gestión</a>
@endsection
