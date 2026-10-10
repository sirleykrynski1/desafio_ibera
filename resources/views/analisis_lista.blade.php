@extends('layouts.app')
@section('content')
<h1 class="h3">Historial de análisis</h1>
<ul class="list-group mb-3">
@forelse($analisis as $informe)
<li class="list-group-item"><a href="{{ route('analisis.mostrar', $informe) }}">Análisis #{{ $informe->getKey() }} — {{ $informe->establecimiento->nombre }}</a> · {{ $informe->estado }} · Dictamen: {{ $informe->resultado_final }}</li>
@empty
<li class="list-group-item">Todavía no hay análisis registrados.</li>
@endforelse
</ul>
{{ $analisis->links() }}
@endsection
