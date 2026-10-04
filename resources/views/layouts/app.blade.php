<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema de Gestión Ambiental de Lixiviados') | Iberá Sustentable</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Estilos ecológicos personalizados -->
    <style>
        :root {
            --eco-green-primary: #198754;
            --eco-green-dark: #145a32;
            --eco-green-light: #e8f5e9;
            --eco-accent: #2e7d32;
        }

        body {
            background-color: #f4f8f5;
            color: #2c3e50;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-eco {
            background: linear-gradient(135deg, #145a32 0%, #1e7e34 100%);
            box-shadow: 0 4px 12px rgba(20, 90, 50, 0.15);
        }

        .card-eco {
            border: 1px solid #c8e6c9;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            background-color: #ffffff;
        }

        .card-eco-header {
            background-color: #f1f8e9;
            border-bottom: 1px solid #dcedc8;
            color: #1b5e20;
            font-weight: 600;
        }

        .btn-eco {
            background-color: #2e7d32;
            color: white;
            border: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-eco:hover {
            background-color: #1b5e20;
            color: white;
            transform: translateY(-1px);
        }

        .map-placeholder {
            background: radial-gradient(circle, #e8f5e9 0%, #c8e6c9 100%);
            border: 2px dashed #81c784;
            border-radius: 12px;
            min-height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        footer {
            margin-top: auto;
            background-color: #ffffff;
            border-top: 1px solid #e0e0e0;
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Barra de navegación principal -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-eco py-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ url('/') }}">
                <span class="fs-4">🌿</span>
                <span>Iberá Sustentable <small class="fw-light fs-6 opacity-75">| Lixiviados</small></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarEcoNav" aria-controls="navbarEcoNav" aria-expanded="false" aria-label="Alternar navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarEcoNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded text-white" href="{{ url('/dashboard') }}">
                            🐊 Dashboard Gobierno
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded text-white" href="{{ url('/establishments') }}">
                            🦦 Mis Hoteles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded text-white" href="{{ url('/maintenance/create') }}">
                            🐸 Registrar Mantenimiento
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container py-4">
        @yield('content')
    </main>

    <!-- Pie de página ecológico -->
    <footer class="py-3 text-center text-muted">
        <div class="container">
            <small>
                💧 Sistema de Control Ambiental de los Humedales del Iberá &bull; Protegiendo nuestros ecosistemas 🦩🌿
            </small>
        </div>
    </footer>

    <!-- Bootstrap 5 JavaScript Bundle con Popper CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
