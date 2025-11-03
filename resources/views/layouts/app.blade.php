<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PharmaSales - Gestión de Ventas')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/css/layout.css', 'resources/css/sidebar.css'])
    @stack('styles')
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" aria-label="Menú">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Overlay para mobile -->
    <div class="sidebar-overlay"></div>
    <!-- Sidebar -->
    <aside class="sidebar">
        <!-- Toggle Button -->
        <button class="sidebar-toggle" aria-label="Colapsar sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="logo-container">
            <div class="logo-text">
                <h1>FUERZA DE VENTA</h1>
            </div>
            <img src="{{ asset('images/logo-medifarma.png') }}" alt="Medifarma" class="logo">
        </div>

        <nav class="nav-section">
            <h3>Navegación</h3>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link" data-tooltip="Dashboard">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('ciclos.index') }}" class="nav-link {{ request()->routeIs('ciclos.*') ? 'active' : '' }}" data-tooltip="Ciclos">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>Ciclos</span>
                    </a>
                </li>
            </ul>
        </nav>
        <nav class="nav-section">
            <h3>FUERZA DE VENTA</h3>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('empleados.index') }}" class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}" data-tooltip="Empleados">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        <span>Empleados</span>
                    </a>
                </li>
            </ul>
        </nav>
        <nav class="nav-section">
            <h3>ZONAS</h3>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('zonas.index') }}" class="nav-link {{ request()->routeIs('zonas.*') ? 'active' : '' }}" data-tooltip="Zonas">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>Zonas</span>
                    </a>
                </li>
            </ul>
        </nav>
        <nav class="nav-section">
            <h3>PRODUCTOS</h3>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('productos.index') }}" class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" data-tooltip="Productos">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        <span>Productos</span>
                    </a>
                </li>
            </ul>
        </nav>
        <nav class="nav-section">
            <h3>HISTORIAL</h3>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('historial.index') }}" class="nav-link {{ request()->routeIs('historial.*') ? 'active' : '' }}" data-tooltip="Historial">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>Historial</span>
                    </a>
                </li>
            </ul>
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    <!-- Vite JS -->
    @vite(['resources/js/sidebar.js'])
    @stack('scripts')
</body>
</html>

