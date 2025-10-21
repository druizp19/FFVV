<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PharmaSales - Gestión de Ventas')</title>
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/css/layout.css', 'resources/css/theme.css', 'resources/css/sidebar.css'])
    @stack('styles')
    
    <!-- Prevenir flash de tema - Ejecutar antes de renderizar -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('pharmasales-theme');
            const systemTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            const theme = savedTheme || systemTheme;
            
            if (theme === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    
    <style>
        /* Prevenir FOUC */
        html {
            visibility: visible;
            opacity: 1;
        }
    </style>
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
                    <a href="#" class="nav-link" data-tooltip="Productos">
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
                    <a href="#" class="nav-link" data-tooltip="Historial">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>Historial</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="theme-toggle-container">
            <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" aria-label="Cambiar tema" data-tooltip="Cambiar tema">
                <svg class="theme-icon sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg class="theme-icon moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <span>Cambiar tema</span>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    <!-- Vite JS -->
    @vite(['resources/js/theme.js', 'resources/js/sidebar.js'])
    @stack('scripts')
</body>
</html>

