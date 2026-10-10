@extends('layouts.app')
@section('content')
<h1>Eco-Iberá</h1>
<p>Seguimiento de análisis de efluentes y apoyo a las inspecciones ambientales.</p>
<h2 class="h4">Establecimientos</h2>
<p>Registrá tu establecimiento, cargá informes de laboratorio y consultá su estado.</p>
<a class="btn btn-success mb-4" href="{{ route('login', ['portal' => 'establecimientos']) }}">Acceso a establecimientos</a>
<h2 class="h4">Gestión</h2>
<p>Revisá análisis, gestioná permisos y consultá alertas climáticas.</p>
<a class="btn btn-outline-success" href="{{ route('login', ['portal' => 'gestion']) }}">Acceso a gestión</a>
@auth
<p class="mt-4"><a href="{{ route('dashboard') }}">Ir a mi panel</a></p>
@endauth
@endsection
