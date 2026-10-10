@extends('layouts.app')
@section('content')
<h1 class="h3">Establecimientos</h1>
@can('create', App\Models\Establecimiento::class)
<a class="btn btn-success mb-3" href="{{ route('establecimientos.crear') }}">Registrar establecimiento</a>
@endcan
<ul class="list-group mb-3">
@forelse($establecimientos as $establecimiento)
<li class="list-group-item"><a href="{{ route('establecimientos.mostrar', $establecimiento) }}">{{ $establecimiento->nombre }}</a> · {{ $establecimiento->ubicacion }}</li>
@empty
<li class="list-group-item">Todavía no hay establecimientos registrados.</li>
@endforelse
</ul>
{{ $establecimientos->links() }}
@endsection
