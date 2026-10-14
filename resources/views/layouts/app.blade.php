<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yporá - Gestión de Efluentes</title>
    
    <!-- Carga de Tailwind, Bootstrap y Estilos vía Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light {{ auth()->check() && !request()->routeIs('login', 'registro', 'inicio') && !request()->is('login*') ? 'with-sidebar' : '' }}">

    @auth
        @if(!request()->routeIs('login', 'registro', 'inicio') && !request()->is('login*'))
            {{-- MENÚ DE NAVEGACIÓN LATERAL RESPONSIVO (IZQUIERDA) --}}
            @include('layouts.navigation')

            {{-- CONTENIDO DINÁMICO ADAPTADO AL SIDEBAR --}}
            <main class="main-content-wrapper p-3 p-md-4">
                <div class="container-fluid px-md-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif
                    @if(session('advertencias'))
                        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                            <ul class="mb-0">
                                @foreach(session('advertencias') as $advertencia)
                                    <li>{{ $advertencia }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        @else
            {{-- VISTA SIN SIDEBAR (LOGIN, REGISTRO, INICIO) --}}
            <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4 shadow-sm">
                <div class="container">
                    <a class="navbar-brand fw-bold" href="{{ route('inicio') }}">🌿 Yporá</a>
                    <div class="ms-auto">
                        <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Ingresar</a>
                    </div>
                </div>
            </nav>
            <div class="container py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        @endif
    @else
        {{-- VISTA PÚBLICA PARA USUARIOS NO AUTENTICADOS --}}
        <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4 shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ route('inicio') }}">🌿 Yporá</a>
                <div class="ms-auto">
                    <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Ingresar</a>
                </div>
            </div>
        </nav>
        <div class="container py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @yield('content')
        </div>
    @endauth

</body>
</html>
