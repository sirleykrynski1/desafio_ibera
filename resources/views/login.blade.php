@extends('layouts.app')
@section('content')
<h1 class="h3">Ingresar</h1>
<form action="{{ route('login.store') }}" method="POST" class="card card-body mb-4">
    @csrf
    <label for="email">Correo</label>
    <input id="email" class="form-control mb-3" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
    <label for="password">Contraseña</label>
    <input id="password" class="form-control mb-3" type="password" name="password" autocomplete="current-password" required>
    <button class="btn btn-success">Ingresar</button>
</form>
<details><summary>Crear cuenta de propietario</summary>
<form action="{{ route('registro') }}" method="POST" class="card card-body mt-3">
    @csrf
    <label for="nombre">Nombre</label><input id="nombre" class="form-control mb-3" name="nombre" value="{{ old('nombre') }}" required>
    <label for="apellido">Apellido</label><input id="apellido" class="form-control mb-3" name="apellido" value="{{ old('apellido') }}" required>
    <label for="registro-email">Correo</label><input id="registro-email" class="form-control mb-3" type="email" name="email" value="{{ old('email') }}" required>
    <label for="registro-password">Contraseña (mínimo 12 caracteres)</label><input id="registro-password" class="form-control mb-3" type="password" name="password" minlength="12" autocomplete="new-password" required>
    <label for="confirmation">Repetir contraseña</label><input id="confirmation" class="form-control mb-3" type="password" name="password_confirmation" autocomplete="new-password" required>
    <button class="btn btn-success">Crear cuenta</button>
</form></details>
@endsection
