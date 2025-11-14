@extends('layouts.app')

@section('title', 'Historial - FFVV')

@section('content')
<div class="historial-container">
    {{-- Header Section --}}
    <header class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">Historial de Cambios</h1>
                <p class="page-description">Registro de todas las modificaciones realizadas por ciclo</p>
            </div>
        </div>
    </header>

    {{-- Filters Section --}}
    <div class="filters-card">
        <div class="filters-header">
            <div class="filters-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="filters-title">Filtros de Búsqueda</h3>
                <p class="filters-description">Filtra el historial por ciclo, entidad, acción o fecha</p>
            </div>
        </div>
        
        <form method="GET" action="{{ route('historial.index') }}" class="filters-form">
            <div class="filters-row">
                <select class="filter-select" name="ciclo" id="ciclo">
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

                <select class="filter-select" name="entidad" id="entidad">
                    <option value="">Todas las entidades</option>
                    @foreach($entidades as $entidad)
                        <option value="{{ $entidad }}" {{ request('entidad') == $entidad ? 'selected' : '' }}>
                            {{ $entidad }}
                        </option>
                    @endforeach
                </select>

                <select class="filter-select" name="accion" id="accion">
                    <option value="">Todas las acciones</option>
                    @foreach($acciones as $accion)
                        <option value="{{ $accion }}" {{ request('accion') == $accion ? 'selected' : '' }}>
                            {{ $accion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filters-row">
                <div class="search-wrapper">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        class="search-input" 
                        name="search"
                        placeholder="Buscar en descripción..."
                        value="{{ request('search') }}"
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Aplicar
                </button>
                
                <a href="{{ route('historial.index') }}" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                    </svg>
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Timeline Container --}}
    <div class="card">
        @if($historial->count() > 0)
            <div class="timeline">
                @foreach($historial as $item)
                <div class="timeline-item">
                    <div class="timeline-marker timeline-marker-{{ $item->getColorAccion() }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            @if($item->accion == 'Crear')
                                <path d="M12 5v14M5 12h14"/>
                            @elseif($item->accion == 'Actualizar')
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            @elseif($item->accion == 'Eliminar')
                                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                            @elseif($item->accion == 'Asignar')
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            @else
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 6v6l4 2"/>
                            @endif
                        </svg>
                    </div>
                    
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div class="timeline-info">
                                <span class="timeline-badge timeline-badge-{{ $item->getColorAccion() }}">
                                    {{ $item->accion }}
                                </span>
                                <span class="timeline-entity">{{ $item->entidad }}</span>
                                @if($item->ciclo)
                                    <span class="timeline-cycle">{{ $item->ciclo->ciclo }}</span>
                                @endif
                            </div>
                            <span class="timeline-date">
                                {{ $item->fechaHora->diffForHumans() }}
                            </span>
                        </div>
                        
                        <p class="timeline-description">{{ $item->descripcion }}</p>
                        
                        <div class="timeline-footer">
                            @if($item->usuario)
                                <div class="timeline-user">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span>{{ $item->usuario }}</span>
                                </div>
                            @endif
                            
                            @if($item->datosAnteriores || $item->datosNuevos)
                                <button class="btn-details" onclick="verDetalles({{ $item->idHistorial }})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Ver detalles
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($historial->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando {{ $historial->firstItem() }} - {{ $historial->lastItem() }} de {{ $historial->total() }} registros
                </div>
                <div class="pagination">
                    {{ $historial->links('vendor.pagination.custom') }}
                </div>
            </div>
            @endif
        @else
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                <h3>No hay registros en el historial</h3>
                <p>No se encontraron cambios con los filtros seleccionados</p>
            </div>
        @endif
    </div>
</div>

{{-- Modal Detalles --}}
<div class="modal" id="detallesModal">
    <div class="modal-overlay" onclick="cerrarDetalles()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Detalles del Cambio</h2>
            <button class="modal-close" onclick="cerrarDetalles()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-body" id="detallesContent">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando detalles...</p>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarDetalles()">Cerrar</button>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div class="toast-container" id="toastContainer"></div>

@endsection

@push('styles')
@vite('resources/css/historial.css')
@endpush

@push('scripts')
@vite('resources/js/historial.js')
<script>
    window.historialData = @json($historial->items());
</script>
@endpush
