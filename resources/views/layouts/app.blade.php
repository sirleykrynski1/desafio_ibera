<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestión de Efluentes - ICAA')</title>
    
    <!-- Carga de Bootstrap y JS vía Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Iconos Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
        }
        .nav-link.active {
            background-color: #e6f4ea;
            color: #137333 !important;
            font-weight: 600;
            border-radius: 8px;
        }
        .nav-link {
            color: #4b5563;
            border-radius: 8px;
            margin-bottom: 4px;
        }
        .nav-link:hover {
            background-color: #f3f4f6;
            color: #111827;
        }
    </style>
</head>
<body class="bg-light">

    <div class="d-flex">
        <!-- Sidebar / Menú Lateral -->
        <aside class="sidebar p-3 d-flex flex-column flex-shrink-0">
            <!-- Brand / Logotipo -->
            <div class="d-flex items-center gap-2 mb-4 px-2">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-droplet-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Gestión de Efluentes</h6>
                    <small class="text-muted" style="font-size: 0.75rem;">ICAA - Corrientes</small>
                </div>
            </div>

            <!-- Navegación del Hotel -->
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door me-2"></i> Inicio
                    </a>
                </li>
                <li>
                    <a href="{{ route('analisis.crear') }}" class="nav-link {{ request()->routeIs('analisis.create') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i> Cargar análisis
                    </a>
                </li>
                <li>
                    <a href="{{ route('analisis.indice') }}" class="nav-link {{ request()->routeIs('analisis.indice') ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-2"></i> Historial
                    </a>
                </li>
                <li>
                    <a href="{{ route('insignias.index') }}" class="nav-link {{ request()->routeIs('insignias.index') ? 'active' : '' }}">
                        <i class="bi bi-award me-2"></i> Insignias
                    </a>
                </li>
            </ul>

            <!-- Perfil del Hotel (Pie de menú) -->
            <div class="pt-3 border-top px-2 d-flex justify-content-between align-items-center text-muted">
                <div>
                    <strong class="d-block text-dark small">Hotel Paraíso</strong>
                    <small style="font-size: 0.7rem;">Establecimiento</small>
                </div>
                <i class="bi bi-chevron-right small"></i>
            </div>
        </aside>

        <!-- Contenido principal dinámico -->
        <main class="flex-grow-1 p-4 overflow-auto" style="height: 100vh;">
            @yield('content')
        </main>
    </div>

</body>
</html>
