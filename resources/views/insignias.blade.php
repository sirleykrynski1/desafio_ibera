@extends('layouts.app')
@section('title', 'Insignias - Panel del Hotel')
@section('content')
<h1 class="h3 mb-3">Tus insignias de cumplimiento</h1>
<p class="text-muted">Insignias otorgadas y registradas para tus establecimientos.</p>
<div class="row g-3">
    @forelse($insignias as $insignia)
    <div class="col-md-6 col-xl-4">
        <article class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h2 class="h5 text-success">{{ $insignia->nombre }}</h2>
                <p>{{ $insignia->establecimiento->nombre }}</p>
                <p class="small text-muted">Otorgada el {{ $insignia->fecha_otorgamiento->format('d/m/Y') }}</p>
                <a href="{{ route('dashboard', ['establecimiento' => $insignia->establecimiento_id]) }}">Ver establecimiento</a>
            </div>
        </article>
    </div>
    @empty
    <div class="col-12"><div class="alert alert-light border">Todavía no tenés insignias otorgadas.</div></div>
    @endforelse
</div>
<div class="mt-4">{{ $insignias->links() }}</div>
@endsection
