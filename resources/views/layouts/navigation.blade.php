@auth
@if(!request()->routeIs('login', 'registro', 'inicio') && !request()->is('login*'))
<div x-data="{ sidebarOpen: false }" class="sidebar-wrapper">
    <!-- BARRA SUPERIOR PARA MÓVILES (md:hidden) -->
    <header class="sidebar-mobile-header d-flex d-md-none align-items-center justify-content-between px-3 py-2 bg-white border-bottom shadow-sm fixed-top">
        <div class="d-flex align-items-center gap-2">
            <!-- Botón Hamburguesa -->
            <button @click="sidebarOpen = !sidebarOpen"
                    type="button"
                    class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center p-2 rounded-2"
                    aria-label="Abrir menú de navegación"
                    :aria-expanded="sidebarOpen.toString()">
                <svg class="bi" width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" style="display: none;" />
                </svg>
            </button>

            <!-- Logo Móvil -->
            <a href="{{ auth()->user()->es('inspector', 'admin_gobierno') ? route('admin.dashboard') : route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-dark">
                <span class="fs-4 me-1 text-success">🌿</span>
                <span class="fw-bold fs-6">Eco-Iberá</span>
            </a>
        </div>

        <!-- Rol Móvil -->
        <div>
            @if(auth()->user()->es('inspector', 'admin_gobierno'))
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small">Gobierno</span>
            @else
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">Propietario</span>
            @endif
        </div>
    </header>

    <!-- TELÓN DE FONDO (Overlay para móviles) -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="sidebar-backdrop d-md-none"
         style="display: none;"></div>

    <!-- MENÚ LATERAL (SIDEBAR) -->
    <aside id="leftSidebar"
           class="left-sidebar bg-white border-end shadow-sm d-flex flex-column"
           :class="{ 'show': sidebarOpen }">
        
        <!-- ENCABEZADO DEL SIDEBAR -->
        <div class="sidebar-header p-3 border-bottom d-flex align-items-center justify-content-between bg-body-tertiary">
            <a href="{{ auth()->user()->es('inspector', 'admin_gobierno') ? route('admin.dashboard') : route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-dark gap-2">
                <div class="brand-icon-wrapper rounded-3 bg-success text-white d-flex align-items-center justify-content-center shadow-sm">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="h6 mb-0 fw-bold text-dark">Eco-Iberá</h1>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        @if(auth()->user()->es('inspector', 'admin_gobierno'))
                            Gestión ICAA Corrientes
                        @else
                            Portal de Establecimientos
                        @endif
                    </small>
                </div>
            </a>

            <!-- Botón cerrar (solo móvil) -->
            <button @click="sidebarOpen = false" type="button" class="btn btn-sm btn-close d-md-none" aria-label="Cerrar menú"></button>
        </div>

        <!-- BADGE DEL ROL -->
        <div class="px-3 pt-3 pb-2">
            @if(auth()->user()->es('inspector', 'admin_gobierno'))
                <div class="p-2 rounded-2 bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center gap-2">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div style="line-height: 1.2;">
                        <span class="d-block fw-semibold small">Inspectores / Gobierno</span>
                        <small class="text-secondary" style="font-size: 10px;">Control & Fiscalización</small>
                    </div>
                </div>
            @else
                <div class="p-2 rounded-2 bg-success-subtle text-success border border-success-subtle d-flex align-items-center gap-2">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <div style="line-height: 1.2;">
                        <span class="d-block fw-semibold small">Rol Propietario</span>
                        <small class="text-secondary" style="font-size: 10px;">Gestión de Efluentes</small>
                    </div>
                </div>
            @endif
        </div>

        <!-- NAVEGACIÓN PRINCIPAL -->
        <nav class="sidebar-nav-list flex-grow-1 px-2 py-2 overflow-y-auto">
            @if(auth()->user()->es('inspector', 'admin_gobierno'))
                {{-- ======================================================== --}}
                {{-- MENÚ PARA INSPECTORES / GOBIERNO (ADMINISTRACIÓN)      --}}
                {{-- ======================================================== --}}
                <div class="sidebar-section-title px-3 pt-2 pb-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">
                    Administración
                </div>

                {{-- 1. /admin/dashboard → resumen: X establecimientos, Y en norma, Z en alerta --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('admin.dashboard') || request()->is('admin/dashboard') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Dashboard</div>
                        <div class="nav-subtitle small {{ request()->routeIs('admin.dashboard') || request()->is('admin/dashboard') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Resumen y métricas
                        </div>
                    </div>
                </a>

                {{-- 2. /admin/detalle-establecimientos → listado completo --}}
                <a href="{{ route('admin.detalle-establecimientos') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('admin.detalle-establecimientos') || request()->is('admin/detalle-establecimientos*') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Establecimientos</div>
                        <div class="nav-subtitle small {{ request()->routeIs('admin.detalle-establecimientos') || request()->is('admin/detalle-establecimientos*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Listado completo
                        </div>
                    </div>
                </a>

                {{-- 3. /admin/alertas → alertas climáticas --}}
                <a href="{{ route('admin.alertas') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('admin.alertas') || request()->is('admin/alertas*') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Alertas Climáticas</div>
                        <div class="nav-subtitle small {{ request()->routeIs('admin.alertas') || request()->is('admin/alertas*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Monitoreo ambiental
                        </div>
                    </div>
                </a>

                {{-- 4. admin/inspectores → inspectores --}}
                <a href="{{ route('admin.inspectores') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('admin.inspectores') || request()->is('admin/inspectores*') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Inspectores</div>
                        <div class="nav-subtitle small {{ request()->routeIs('admin.inspectores') || request()->is('admin/inspectores*') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Operaciones ICAA
                        </div>
                    </div>
                </a>

                <!-- Opciones complementarias de gestión -->
                <div class="sidebar-section-title px-3 pt-3 pb-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">
                    Fiscalización
                </div>

                <a href="{{ route('gestion') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('gestion') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Revisión de Análisis</div>
                        <div class="nav-subtitle small {{ request()->routeIs('gestion') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Informes pendientes
                        </div>
                    </div>
                </a>

            @else
                {{-- ======================================================== --}}
                {{-- MENÚ PARA PROPIETARIOS / ESTABLECIMIENTOS                --}}
                {{-- ======================================================== --}}
                <div class="sidebar-section-title px-3 pt-2 pb-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">
                    Mi Establecimiento
                </div>

                {{-- 1. /dashboard → semáforo de cumplimiento actual --}}
                <a href="{{ route('dashboard') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('dashboard') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Dashboard</div>
                        <div class="nav-subtitle small {{ request()->routeIs('dashboard') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Semáforo de cumplimiento actual
                        </div>
                    </div>
                </a>

                {{-- 2. /analisis → formulario para cargar análisis --}}
                <a href="{{ route('analisis.crear') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('analisis.crear') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Cargar Análisis</div>
                        <div class="nav-subtitle small {{ request()->routeIs('analisis.crear') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Formulario de carga de informe
                        </div>
                    </div>
                </a>

                {{-- 3. /historial → tabla de análisis previos --}}
                <a href="{{ route('analisis.indice') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('analisis.indice') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Historial</div>
                        <div class="nav-subtitle small {{ request()->routeIs('analisis.indice') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Tabla de análisis previos
                        </div>
                    </div>
                </a>

                <!-- Opciones generales del propietario -->
                <div class="sidebar-section-title px-3 pt-3 pb-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">
                    Mi Cuenta
                </div>

                <a href="{{ route('establecimientos.indice') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('establecimientos.indice') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Mis Establecimientos</div>
                        <div class="nav-subtitle small {{ request()->routeIs('establecimientos.indice') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Datos y permisos de vuelco
                        </div>
                    </div>
                </a>

                <a href="{{ route('insignias.index') }}"
                   class="sidebar-nav-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none my-1 {{ request()->routeIs('insignias.index') ? 'active bg-success text-white' : 'text-dark hover-bg' }}">
                    <div class="nav-icon me-3 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">Insignias Ecológicas</div>
                        <div class="nav-subtitle small {{ request()->routeIs('insignias.index') ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                            Reconocimiento ambiental
                        </div>
                    </div>
                </a>
            @endif
        </nav>

        <!-- SECCIÓN DE PERFIL Y SESIÓN (PIE DEL SIDEBAR) -->
        <div class="sidebar-footer p-3 border-top bg-light">
            <div class="d-flex align-items-center mb-2">
                <div class="user-avatar rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 38px; height: 38px; font-size: 13px;">
                    {{ strtoupper(substr(Auth::user()->nombre ?? 'U', 0, 1)) }}{{ strtoupper(substr(Auth::user()->apellido ?? '', 0, 1)) }}
                </div>
                <div class="overflow-hidden flex-grow-1">
                    <div class="fw-bold small text-truncate text-dark">{{ Auth::user()->nombre . " " . Auth::user()->apellido }}</div>
                    <div class="text-muted text-truncate" style="font-size: 11px;">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary-subtle">
                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 py-1 px-2" style="font-size: 11px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Perfil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 py-1 px-2" style="font-size: 11px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Salir</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
</div>
@endif
@endauth
