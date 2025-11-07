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
    @vite(['resources/css/app.css', 'resources/css/layout.css', 'resources/css/sidebar.css', 'resources/css/modals.css'])
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
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}" data-tooltip="Dashboard">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="9" x="3" y="3" rx="1"/>
                            <rect width="7" height="5" x="14" y="3" rx="1"/>
                            <rect width="7" height="9" x="14" y="12" rx="1"/>
                            <rect width="7" height="5" x="3" y="16" rx="1"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('ciclos.index') }}" class="nav-link {{ request()->routeIs('ciclos.*') ? 'active' : '' }}" data-tooltip="Ciclos">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 2v4"/>
                            <path d="M16 2v4"/>
                            <rect width="18" height="18" x="3" y="4" rx="2"/>
                            <path d="M3 10h18"/>
                        </svg>
                        <span>Ciclos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('empleados.index') }}" class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}" data-tooltip="Empleados">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>Empleados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('zonas.index') }}" class="nav-link {{ request()->routeIs('zonas.*') ? 'active' : '' }}" data-tooltip="Zonas">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>Zonas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('productos.index') }}" class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" data-tooltip="Productos">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m7.5 4.27 9 5.15"/>
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                            <path d="m3.3 7 8.7 5 8.7-5"/>
                            <path d="M12 22V12"/>
                        </svg>
                        <span>Productos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('historial.index') }}" class="nav-link {{ request()->routeIs('historial.*') ? 'active' : '' }}" data-tooltip="Historial">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                            <path d="M12 7v5l4 2"/>
                        </svg>
                        <span>Historial</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- User Profile Section -->
        <div class="user-profile">
            <div class="user-avatar">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(userName()) }}&background=667eea&color=fff&size=128" alt="{{ userName() }}">
            </div>
            <div class="user-info">
                <div class="user-name">{{ userName() }}</div>
                <div class="user-role">{{ ucfirst(azureUser()['rol'] ?? 'Usuario') }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="Cerrar sesión">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>

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

