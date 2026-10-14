@extends('layouts.app')
@section('content')
<h1 class="h3 mb-3">Resumen del Establecimiento</h1>
@if($establecimiento)
<form method="GET" action="{{ route('dashboard') }}" class="mb-4">
    <label for="establecimiento" class="form-label">Establecimiento</label>
    <div class="d-flex gap-2">
        <select id="establecimiento" name="establecimiento" class="form-select">
            @foreach($establecimientos as $opcion)
            <option value="{{ $opcion->id }}" @selected($opcion->id === $establecimiento->id)>{{ $opcion->nombre }}</option>
            @endforeach
        </select>
        <button class="btn btn-success">Ver resumen</button>
    </div>
</form>
<h2 class="h4 mb-4">{{ $establecimiento->nombre }}</h2>
<div class="row g-4">
    <div class="col-lg-8">
        <section class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h3 class="h5">Último análisis de laboratorio</h3>
                <div class="alert alert-{{ $colorEvaluacion }} mt-3">
                    <strong>{{ $evaluacion }}</strong>
                    @if($analisis)<p class="mb-0 mt-2">Resultado automático. El dictamen humano se muestra por separado.</p>@endif
                </div>
                @if($analisis)
                <p>Fecha de muestra: {{ $analisis->fecha_muestra->format('d/m/Y') }}</p>
                <p>Dictamen final: <strong>{{ $analisis->resultado_final }}</strong></p>
                <a href="{{ route('analisis.mostrar', $analisis) }}" class="btn btn-outline-success">Ver análisis #{{ $analisis->getKey() }}</a>
                @else
                <p>Cargá un informe para consultar su evaluación.</p>
                <a href="{{ route('analisis.crear') }}" class="btn btn-outline-success">Cargar análisis</a>
                @endif
            </div>
        </section>
        <section class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h3 class="h5 mb-0">Permiso de vuelco</h3>
                    <span class="badge rounded-pill {{ $estadoPermiso === 'Activo' ? 'bg-success' : ($permiso ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        {{ $estadoPermiso ?? 'Sin registrar' }}
                    </span>
                </div>
                @if($permiso)
                <dl class="row mb-3">
                    <dt class="col-sm-5 text-muted fw-normal">Expediente</dt>
                    <dd class="col-sm-7 text-break">{{ $permiso->numero_expediente }}</dd>
                    <dt class="col-sm-5 text-muted fw-normal">Fecha de emisión</dt>
                    <dd class="col-sm-7">{{ $permiso->fecha_emision->format('d/m/Y') }}</dd>
                    <dt class="col-sm-5 text-muted fw-normal">Vencimiento</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $permiso->fecha_vencimiento->format('d/m/Y') }}</dd>
                    <dt class="col-sm-5 text-muted fw-normal">Estado del trámite</dt>
                    <dd class="col-sm-7">{{ ucfirst(str_replace('_', ' ', $permiso->estado_tramite)) }}</dd>
                </dl>
                @else
                <div class="bg-light border rounded-3 p-3 mb-3">
                    <p class="fw-semibold mb-2">Sin permiso registrado.</p>
                    <p class="text-muted mb-0">Todavía no hay un expediente ni una fecha de vencimiento cargados para este establecimiento. El equipo de gestión debe registrar esa información.</p>
                </div>
                @endif
                <p class="small text-muted">La carga de un análisis no otorga ni renueva el permiso automáticamente.</p>
                <a href="{{ route('establecimientos.mostrar', $establecimiento) }}" class="btn btn-outline-success">Ver datos del establecimiento</a>
            </div>
        </section>
        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="h5">Insignias de cumplimiento</h3>
                @forelse($insignias as $insignia)
                <div class="border-bottom py-3">
                    <strong class="text-success">{{ $insignia->nombre }}</strong>
                    <p class="small text-muted mb-0">Otorgada el {{ $insignia->fecha_otorgamiento->format('d/m/Y') }}</p>
                </div>
                @empty
                <p class="text-muted">Todavía no hay insignias otorgadas a este establecimiento.</p>
                @endforelse
                <a href="{{ route('insignias.index') }}" class="d-inline-block mt-3">Ver mis insignias</a>
            </div>
        </section>
    </div>
    <div class="col-lg-4">
        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="h5">Alertas climáticas registradas</h3>
                <p class="small text-muted">Hasta 5 avisos con períodos que todavía no finalizaron. La fecha de consulta indica cuándo se obtuvo cada pronóstico.</p>
                @forelse($alertas as $alerta)
                <div class="alert alert-warning">
                    <strong>{{ str_replace('_', ' ', $alerta->tipo) }}</strong>
                    <p class="small mb-1">Desde {{ $alerta->fecha_evento->format('d/m/Y') }} hasta {{ ($alerta->periodo_hasta ?? $alerta->fecha_evento)->format('d/m/Y') }}</p>
                    <p class="small mb-1">Precipitación prevista: {{ $alerta->milimetros_lluvia }} mm</p>
                    <p class="small mb-0">Consulta: {{ $alerta->consultado_en?->format('d/m/Y H:i') ?? 'Sin fecha registrada' }}</p>
                </div>
                @empty
                <p>Sin alertas registradas para períodos vigentes. Esto no confirma ausencia de riesgo.</p>
                @endforelse
                <a href="{{ route('establecimientos.clima', $establecimiento) }}" class="btn btn-outline-success">Consultar pronóstico</a>
            </div>
        </section>
    </div>
</div>
@else
<div class="card shadow-sm border-0"><div class="card-body p-4">
    <h2 class="h5">Todavía no tenés establecimientos registrados</h2>
    <p>Registrá tu establecimiento para consultar sus análisis, permisos e insignias.</p>
    <a href="{{ route('establecimientos.crear') }}" class="btn btn-success">Registrar establecimiento</a>
</div></div>
@endif
@endsection
