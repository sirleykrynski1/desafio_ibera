@extends('layouts.app')
@section('title', 'Panel Ambiental')
@section('content')
<h1 class="h3 mb-4">Panel de Control Ambiental</h1>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card card-body"><h2 class="h6">Establecimientos registrados</h2><strong>{{ $estadisticas['total_establecimientos'] }}</strong></div></div>
    <div class="col-md-4"><div class="card card-body"><h2 class="h6">Riesgo rojo registrado</h2><strong>{{ $estadisticas['conteo_riesgos']['rojo'] }}</strong></div></div>
    <div class="col-md-4"><div class="card card-body"><h2 class="h6">Sin evaluación de riesgo</h2><strong>{{ $estadisticas['conteo_riesgos']['sin_evaluar'] }}</strong></div></div>
</div>
<p class="text-muted">Últimas evaluaciones registradas. No representan una medición en tiempo real.</p>
<div class="table-responsive"><table class="table">
    <thead><tr><th>Establecimiento</th><th>Riesgo registrado</th><th>Fecha de evaluación</th></tr></thead>
    <tbody>
    @forelse($establecimientos as $establecimiento)
    <tr><td>{{ $establecimiento->nombre }}</td><td>{{ $establecimiento->ultimoRiesgo?->nivel_riesgo ?? 'Sin evaluar' }}</td><td>{{ $establecimiento->ultimoRiesgo?->fecha_evaluacion?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td></tr>
    @empty
    <tr><td colspan="3">Sin establecimientos registrados.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
