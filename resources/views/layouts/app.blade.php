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
            <div class="logo">📦</div>
            <div class="logo-text">
                <h1>PharmaSales</h1>
                <p>Gestión de Ventas</p>
            </div>
        </div>

        <nav class="nav-section">
            <h3>Navegación</h3>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link" data-tooltip="Dashboard">
                        <span class="nav-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('ciclos.index') }}" class="nav-link {{ request()->routeIs('ciclos.*') ? 'active' : '' }}" data-tooltip="Ciclos">
                        <span class="nav-icon">📅</span>
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
                        <span class="nav-icon">👥</span>
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
                        <span class="nav-icon">📍</span>
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
                        <span class="nav-icon">📦</span>
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
                        <span class="nav-icon">🕐</span>
                        <span>Historial</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="theme-toggle-container">
            <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" aria-label="Cambiar tema" data-tooltip="Cambiar tema">
                <span class="theme-icon">☀️</span>
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

