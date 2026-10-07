<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Hotel - Eco-Iberá</title>
    
    <!-- Carga de Bootstrap vía Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Gestión de Efluentes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir o cerrar navegación">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('establecimientos.indice') }}">Establecimientos</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('analisis.indice') }}">Historial</a></li>
                    <li class="nav-item"><form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-outline-light" type="submit">Salir</button></form></li>
                    @can('create', App\Models\Establecimiento::class)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('analisis.crear') }}">Cargar Análisis</a>
                    </li>
                    @endcan
                    @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Ingresar</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido dinámico -->
    <div class="container">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if(session('advertencias'))
        <div class="alert alert-warning"><ul class="mb-0">@foreach(session('advertencias') as $advertencia)<li>{{ $advertencia }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </div>

</body>
</html>
