@extends('layouts.app')

@section('title', 'Zonas - PharmaSales')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-text">
            <h1 class="page-title">Gestión de Zonas</h1>
            <p class="page-subtitle">Administra las zonas geográficas y sus asignaciones</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('create')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Nueva Zona</span>
        </button>
    </div>
</div>

{{-- Filters Section --}}
<div class="filters-section">
    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Ciclo</span>
        </div>
        <select class="filter-select" id="cycleFilter" onchange="filterByCycle()">
            <option value="">Todos los ciclos</option>
            @foreach($ciclos as $ciclo)
                @php
                    $esCerrado = false;
                    if ($ciclo->fechaFin) {
                        $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                        $hoy = \Carbon\Carbon::now()->startOfDay();
                        $esCerrado = $fechaFin->lt($hoy);
                    }
                    if ($ciclo->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                    $estadoTexto = $esCerrado ? 'Cerrado' : 'Activo';
                @endphp
                <option value="{{ $ciclo->idCiclo }}" data-cerrado="{{ $esCerrado ? 'true' : 'false' }}" {{ $cicloSeleccionado == $ciclo->idCiclo ? 'selected' : '' }}>
                    {{ $ciclo->ciclo }} ({{ $estadoTexto }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <span>Buscar</span>
        </div>
        <input type="text" class="filter-input" id="searchInput" placeholder="Buscar zona..." onkeyup="searchZones()">
    </div>
</div>

{{-- Warning for closed cycle --}}
<div class="alert alert-warning" id="cycleClosedWarning" style="display: none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
        <line x1="12" y1="9" x2="12" y2="13"></line>
        <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>
    <span>Este ciclo está cerrado. No se pueden realizar modificaciones.</span>
</div>

{{-- Zones Table --}}
<div class="zones-table-container">
    <div class="table-wrapper">
        <table class="zones-table" id="zonesTable">
            <thead>
                <tr>
                    <th>Zona</th>
                    <th>Estado</th>
                    <th>Empleados</th>
                    <th>Geosegmentos</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody id="zonesTableBody">
                @forelse($zonas as $zona)
                    <tr data-zone-id="{{ $zona->idZona }}" data-zone-name="{{ $zona->zona }}">
                        <td>
                            <div class="zone-name">
                                <div class="zone-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <span class="zone-text">{{ $zona->zona }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $estadoNombre = 'Inactivo';
                                if ($zona->estado && is_object($zona->estado)) {
                                    $estadoNombre = $zona->estado->estado ?? 'Inactivo';
                                }
                            @endphp
                            <span class="status-badge status-{{ strtolower($estadoNombre) }}">
                                <span class="status-dot"></span>
                                {{ $estadoNombre }}
                            </span>
                        </td>
                        <td>
                            <div class="count-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>{{ $zona->zonasEmpleados->count() }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="count-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>{{ $zona->zonasGeosegmentos->count() }}</span>
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <button class="action-btn action-view" onclick="viewZoneDetails({{ $zona->idZona }})" title="Ver detalles">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="action-btn action-edit" onclick="openModal('edit', {{ $zona->idZona }})" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button class="action-btn action-delete" onclick="confirmDeactivate({{ $zona->idZona }}, '{{ $zona->zona }}')" title="Desactivar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <p>No hay zonas registradas</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
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

{{-- Modal Crear/Editar Zona --}}
<div class="modal" id="zoneModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nueva Zona</h2>
                <button class="modal-close" onclick="closeModal()" type="button">&times;</button>
            </div>

            <form id="zoneForm" onsubmit="saveZone(event)">
                <div class="modal-body">
                    <input type="hidden" id="zoneId">

                    <div class="form-group">
                        <label class="form-label">Nombre de la Zona <span class="required">*</span></label>
                        <input type="text" class="form-input" id="zona" required placeholder="Ej: Zona Norte">
                    </div>

                    <div class="form-group" id="estadoGroup" style="display: none;">
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

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Ver Detalles --}}
<div class="modal" id="detailsModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content details-modal-content">
            <div class="modal-header">
                <h2 id="detailsTitle">Detalles de la Zona</h2>
                <button class="modal-close" onclick="closeDetailsModal()" type="button">&times;</button>
            </div>

            <div id="detailsContent">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Cargando detalles...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Confirmar Acción --}}
<div class="modal" id="confirmModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="confirmTitle">Confirmar Acción</h2>
                <button class="modal-close" onclick="closeConfirmModal()" type="button">&times;</button>
            </div>

            <div class="modal-body">
                <p class="confirm-message" id="confirmMessage"></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancelar</button>
                <button class="btn btn-danger" id="confirmButton" onclick="executeConfirmAction()">
                    <span id="confirmButtonText">Confirmar</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Agregar Empleado --}}
<div class="modal" id="addEmpleadoModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Agregar Empleados</h2>
                <button class="modal-close" onclick="closeAddEmpleadoModal()" type="button">&times;</button>
            </div>

            <div class="modal-body">
                {{-- Buscador --}}
                <div class="form-group">
                    <input 
                        type="text" 
                        class="form-input" 
                        id="empSearchInput" 
                        placeholder="Buscar empleado..." 
                        onkeyup="searchEmpleados()"
                        autocomplete="off"
                    >
                </div>

                {{-- Lista de Empleados --}}
                <div class="emp-list-container" id="empListContainer">
                    <div class="emp-list" id="empList">
                        <div class="geo-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <p>Escribe para buscar empleados...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddEmpleadoModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmSaveEmpleados()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Agregar Geosegmento --}}
<div class="modal" id="addGeosegmentoModal">
    <div class="modal-dialog modal-geo-horizontal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Agregar Geosegmentos</h2>
                <button class="modal-close" onclick="closeAddGeosegmentoModal()" type="button">&times;</button>
            </div>

            <div class="modal-body">
                {{-- Buscador --}}
                <div class="geo-search-wrapper">
                    <div class="geo-search-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input 
                            type="text" 
                            class="geo-search-input" 
                            id="geoSearchInput" 
                            placeholder="Buscar geosegmento..." 
                            onkeyup="searchGeosegmentos()"
                            autocomplete="off"
                        >
                    </div>
                </div>

                {{-- Grid de Geosegmentos --}}
                <div class="geo-grid-container">
                    <div class="geo-grid" id="geoGrid">
                        <div class="geo-loading">
                            <div class="spinner"></div>
                            <p>Cargando geosegmentos...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddGeosegmentoModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmSaveGeosegmentos()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toast-container" class="toast-container"></div>

{{-- Scripts --}}
<script>
    window.geosegmentosData = @json($geosegmentos);
    window.ciclosData = @json($ciclos);
    window.empleadosData = @json($empleados);
</script>
@endsection

@push('styles')
@vite('resources/css/zonas.css')
@endpush

@push('scripts')
@vite('resources/js/zonas.js')
@endpush
