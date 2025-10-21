@extends('layouts.app')

@section('title', 'Zonas - PharmaSales')

@section('content')
<div class="zones-container">
    {{-- Header Section --}}
    <header class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">Gestión de Zonas</h1>
                <p class="page-description">Administra las zonas geográficas y sus asignaciones por ciclo</p>
            </div>
            <button class="btn btn-primary" onclick="openModal('create')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nueva Zona
            </button>
        </div>
    </header>

    {{-- Cycle Selector --}}
    <div class="cycle-selector-card">
        <div class="cycle-selector-header">
            <div class="cycle-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
            <div>
                <h3 class="cycle-title">Seleccionar Ciclo</h3>
                <p class="cycle-description">Filtra las asignaciones por ciclo específico</p>
            </div>
        </div>
        <select class="cycle-select" id="cycleFilter" onchange="filterByCycle()">
            <option value="">Todos los ciclos</option>
            @foreach($ciclos as $ciclo)
                <option value="{{ $ciclo->idCiclo }}" {{ request('ciclo') == $ciclo->idCiclo ? 'selected' : '' }}>
                    {{ $ciclo->ciclo }}
                    @if($ciclo->relationLoaded('estado') && $ciclo->getRelation('estado'))
                        - {{ $ciclo->getRelation('estado')->estado }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    {{-- Search Bar --}}
    <div class="toolbar">
        <div class="search-wrapper">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <input 
                type="text" 
                class="search-input" 
                placeholder="Buscar zona por nombre..."
                id="searchInput"
                value="{{ request('search') }}"
                oninput="searchZones()"
            >
        </div>

        <button class="btn btn-secondary btn-icon" onclick="clearFilters()" title="Limpiar filtros">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
            </svg>
        </button>
    </div>

    {{-- Table Container --}}
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Zona</th>
                    <th>Empleados</th>
                    <th>Geosegmentos</th>
                    <th>Ubigeos</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($zonas as $zona)
                <tr>
                    <td>
                        <span class="badge badge-id">{{ $zona->idZona }}</span>
                    </td>
                    <td>
                        <div class="zone-info">
                            <span class="zone-name">{{ $zona->zona }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="stat-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            <span>{{ $zona->zonasEmpleados->count() }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="stat-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>{{ $zona->zonasGeosegmentos->count() }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="stat-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                            </svg>
                            <span>
                                @php
                                    $ubigeoCount = 0;
                                    foreach($zona->zonasGeosegmentos as $zg) {
                                        if ($zg->geosegmento) {
                                            $ubigeoCount += $zg->geosegmento->ubigeos->count() ?? 0;
                                        }
                                    }
                                @endphp
                                {{ $ubigeoCount }}
                            </span>
                        </div>
                    </td>
                    <td>
                        @if($zona->idEstado == 1)
                            <span class="status status-active">Activo</span>
                        @else
                            <span class="status status-inactive">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-action-view" onclick="viewZoneDetails({{ $zona->idZona }})" title="Ver detalles">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button class="btn-action btn-action-edit" onclick="editZone({{ $zona->idZona }})" title="Editar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            @if($zona->idEstado == 1)
                            <button class="btn-action btn-action-delete" onclick="confirmDeactivate({{ $zona->idZona }}, '{{ $zona->zona }}')" title="Desactivar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    <path d="M10 11v6M14 11v6"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                        <h3>No hay zonas disponibles</h3>
                        <p>Comienza creando una nueva zona</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($zonas->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando {{ $zonas->firstItem() }} - {{ $zonas->lastItem() }} de {{ $zonas->total() }} zonas
            </div>
            <div class="pagination">
                {{ $zonas->links('vendor.pagination.custom') }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal Crear/Editar Zona --}}
<div class="modal" id="zoneModal">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Nueva Zona</h2>
            <button class="modal-close" onclick="closeModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="zoneForm" onsubmit="saveZone(event)">
            <div class="modal-body">
                <input type="hidden" id="zoneId">

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Nombre de la Zona <span class="required">*</span></label>
                        <input type="text" class="form-input" id="zona" required placeholder="Ej: Zona Norte">
                    </div>

                    <div class="form-group full-width" id="estadoGroup" style="display: none;">
                        <label class="form-label">Estado <span class="required">*</span></label>
                        <select class="form-select" id="idEstado">
                            @foreach($estados as $estado)
                                <option value="{{ $estado->idEstado }}" {{ $estado->estado == 'Activo' ? 'selected' : '' }}>
                                    {{ $estado->estado }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <path d="M17 21v-8H7v8M7 3v5h8"/>
                    </svg>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Ver Detalles --}}
<div class="modal" id="detailsModal">
    <div class="modal-overlay" onclick="closeDetailsModal()"></div>
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h2 class="modal-title" id="detailsTitle">Detalles de la Zona</h2>
            <button class="modal-close" onclick="closeDetailsModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-body">
            <div id="detailsContent">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Cargando detalles...</p>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDetailsModal()">Cerrar</button>
        </div>
    </div>
</div>

{{-- Modal Confirmar Desactivación --}}
<div class="modal" id="confirmModal">
    <div class="modal-overlay" onclick="closeConfirmModal()"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h2 class="modal-title">Confirmar Desactivación</h2>
            <button class="modal-close" onclick="closeConfirmModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-body">
            <div class="confirm-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
            </div>
            <p class="confirm-message" id="confirmMessage"></p>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancelar</button>
            <button class="btn btn-danger" onclick="deactivateZone()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
                Desactivar
            </button>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div class="toast-container" id="toastContainer"></div>

@endsection

@push('styles')
@vite('resources/css/zonas.css')
@endpush

@push('scripts')
@vite('resources/js/zonas.js')
@endpush
