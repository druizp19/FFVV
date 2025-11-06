@extends('layouts.app')

@section('title', 'Dashboard - PharmaSales')

@section('content')
<div class="dashboard-container">
    {{-- Header Section --}}
    <header class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-description">Resumen general del sistema de fuerza de ventas</p>
            </div>
        </div>
    </header>

    {{-- Cycle Filter --}}
    <div class="cycle-filter-card">
        <div class="cycle-filter-header">
            <div class="cycle-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div>
                <h3 class="cycle-title">Filtrar por Ciclo</h3>
                <p class="cycle-description">Visualiza estadísticas de un ciclo específico</p>
            </div>
        </div>
        <form method="GET" action="{{ route('dashboard.index') }}" class="cycle-filter-form">
            <select class="cycle-select" name="ciclo" id="cicloFilter" onchange="this.form.submit()">
                <option value="">Todos los ciclos</option>
                @foreach($ciclos as $c)
                    @php
                        // Determinar si el ciclo está cerrado
                        $esCerrado = false;
                        
                        // Verificar por fecha de fin
                        if ($c->fechaFin) {
                            $fechaFin = \Carbon\Carbon::parse($c->fechaFin)->startOfDay();
                            $hoy = \Carbon\Carbon::now()->startOfDay();
                            $esCerrado = $fechaFin->lt($hoy);
                        }
                        
                        // Fallback: verificar por estado si no hay fecha
                        if (!$esCerrado && $c->relationLoaded('estado') && $c->getRelation('estado')) {
                            $estadoRelacion = $c->getRelation('estado');
                            if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                                $esCerrado = true;
                            }
                        }
                        
                        $estadoTexto = $esCerrado ? 'Cerrado' : ($c->relationLoaded('estado') && $c->getRelation('estado') ? $c->getRelation('estado')->estado : '');
                    @endphp
                    <option value="{{ $c->idCiclo }}" {{ request('ciclo') == $c->idCiclo ? 'selected' : '' }}>
                        {{ $c->ciclo ?? $c->idCiclo }}@if($estadoTexto) - {{ $estadoTexto }}@endif
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Ciclos Totales</p>
                <h3 class="stat-value">{{ $estadisticas['totalCiclos'] }}</h3>
                <p class="stat-detail">{{ $estadisticas['ciclosActivos'] }} activos</p>
            </div>
        </div>

        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Zonas Activas</p>
                <h3 class="stat-value">{{ $estadisticas['totalZonas'] }}</h3>
                <p class="stat-detail">En operación</p>
            </div>
        </div>

        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                    <path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Empleados</p>
                <h3 class="stat-value">{{ $estadisticas['totalEmpleados'] }}</h3>
                <p class="stat-detail">Fuerza de ventas</p>
            </div>
        </div>

        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Productos</p>
                <h3 class="stat-value">{{ $estadisticas['totalProductos'] }}</h3>
                <p class="stat-detail">Activos</p>
            </div>
        </div>
    </div>

    {{-- Actividad Reciente --}}
    <div class="activity-section">
        <div class="activity-header">
            <h3 class="activity-title">Actividad Reciente</h3>
            <p class="activity-description">Últimas acciones realizadas en el sistema</p>
        </div>
        <div class="activity-list">
            @forelse($actividadReciente as $actividad)
            <div class="activity-item">
                <div class="activity-icon activity-icon-{{ strtolower($actividad['accion']) }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        @if($actividad['accion'] == 'Crear')
                            <path d="M12 5v14M5 12h14"/>
                        @elseif($actividad['accion'] == 'Actualizar')
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        @else
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        @endif
                    </svg>
                </div>
                <div class="activity-content">
                    <p class="activity-description">{{ $actividad['descripcion'] }}</p>
                    <div class="activity-meta">
                        <span class="activity-entity">{{ $actividad['entidad'] }}</span>
                        <span class="activity-separator">•</span>
                        <span class="activity-time">{{ \Carbon\Carbon::parse($actividad['fechaHora'])->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-activity">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                <p>No hay actividad reciente</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/dashboard.css')
@endpush

@push('scripts')
@vite('resources/js/dashboard.js')
@endpush
