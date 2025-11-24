@extends('layouts.app')

@section('title', 'Zonas - FFVV')

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
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline>
                <polyline points="7.5 19.79 7.5 14.6 3 12"></polyline>
                <polyline points="21 12 16.5 14.6 16.5 19.79"></polyline>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            <span>Línea</span>
        </div>
        <select class="filter-select" id="lineaFilter" onchange="filterByLinea()" data-selected="{{ request('linea') }}">
            <option value="">Todas las líneas</option>
        </select>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <span>Buscar Zona</span>
        </div>
        <input type="text" class="filter-input" id="searchInput" placeholder="Buscar zona..." value="{{ request('search') }}" onkeyup="searchZones()">
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Buscar Empleado</span>
        </div>
        <input type="text" class="filter-input" id="empleadoSearchInput" placeholder="Buscar supervisor o representante..." value="{{ request('empleado') }}" onkeyup="searchByEmpleado()">
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
                    <th>Supervisores</th>
                    <th>Representantes</th>
                    <th>Geosegmentos</th>
                    <th>Ubigeo</th>
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
                            <div class="count-badge count-badge-supervisor">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>{{ $zona->supervisores_count ?? 0 }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="count-badge count-badge-representante">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>{{ $zona->representantes_count ?? 0 }}</span>
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
                        <td>
                            <div class="count-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                                <span>{{ $zona->ubigeos_count }}</span>
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
                        <td colspan="7" class="empty-state">
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
                {{-- Tipo de Empleado --}}
                <div class="form-group">
                    <label class="form-label">Tipo de Empleado</label>
                    <select class="form-select" id="tipoEmpleado" onchange="handleTipoEmpleadoChange()">
                        <option value="supervisor">Supervisor</option>
                        <option value="representante">Representante Médico</option>
                    </select>
                </div>

                {{-- Selector de Supervisor (solo para representantes) --}}
                <div class="form-group" id="supervisorGroup" style="display: none;">
                    <label class="form-label">Supervisor <span class="required">*</span></label>
                    <select class="form-select" id="supervisorSelect">
                        <option value="">Seleccione un supervisor...</option>
                    </select>
                </div>

                {{-- Selector de FranqLinea (solo para representantes) --}}
                <div class="form-group" id="franqLineaGroup" style="display: none;">
                    <label class="form-label">Franquicia/Línea <span class="required">*</span></label>
                    <select class="form-select" id="franqLineaSelect">
                        <option value="">Seleccione una franquicia/línea...</option>
                    </select>
                </div>

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

{{-- Modal Unificar Línea --}}
<div class="modal" id="unificarLineaModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon unify-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                        </svg>
                    </div>
                    <div>
                        <h2>Unificar Líneas</h2>
                        <p class="modal-subtitle">Combina dos líneas en una sola</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeUnificarLineaModal()" type="button">&times;</button>
            </div>

            <div class="modal-body">
                {{-- Líneas Actuales --}}
                <div class="unify-section">
                    <div class="unify-section-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span>Líneas a Unificar</span>
                    </div>
                    <div class="unify-lineas-list" id="unifyLineasList">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Cargando líneas...</p>
                        </div>
                    </div>
                </div>

                {{-- Nueva Línea --}}
                <div class="unify-section">
                    <div class="unify-section-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <span>Nueva Línea (FranqLinea)</span>
                        </div>
                    <div class="form-group">
                        <label class="form-label">Seleccione la nueva línea <span class="required">*</span></label>
                        <select class="form-select" id="nuevaLineaSelect">
                            <option value="">Seleccione una línea...</option>
                        </select>
                    </div>
                </div>

                {{-- Empleado para la Nueva Línea --}}
                <div class="unify-section">
                    <div class="unify-section-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Empleado Asignado</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Buscar empleado <span class="required">*</span></label>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="unifyEmpleadoSearch" 
                            placeholder="Buscar por nombre o código..."
                            onkeyup="searchEmpleadosUnify()"
                            autocomplete="off"
                        >
                    </div>
                    <div class="unify-empleado-list" id="unifyEmpleadoList">
                        <div class="geo-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <p>Escribe para buscar empleados...</p>
                        </div>
                    </div>
                    <div class="unify-empleado-selected" id="unifyEmpleadoSelected" style="display: none;"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUnificarLineaModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmUnificarLinea()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                    </svg>
                    Unificar Líneas
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
                <div class="modal-header-actions">
                    <button class="btn-create-geo" onclick="openCreateGeosegmentoModal()" type="button" title="Crear nuevo geosegmento">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Crear Geosegmento</span>
                    </button>
                    <button class="modal-close" onclick="closeAddGeosegmentoModal()" type="button">&times;</button>
                </div>
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

{{-- Modal Crear Geosegmento --}}
<div class="modal" id="createGeosegmentoModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Geosegmento</h2>
                <button class="modal-close" onclick="closeCreateGeosegmentoModal()" type="button">&times;</button>
            </div>

            <form id="createGeosegmentoForm" onsubmit="saveNewGeosegmento(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nombre del Geosegmento <span class="required">*</span></label>
                        <input type="text" class="form-input" id="newGeosegmento" required placeholder="Ej: LIMA CENTRO">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lugar</label>
                        <input type="text" class="form-input" id="newLugar" placeholder="Ej: Lima, Perú">
                        <small class="form-hint">Ubicación geográfica del geosegmento (opcional)</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateGeosegmentoModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Crear
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Asignar Ubigeos a Geosegmento --}}
<div class="modal" id="assignUbigeosModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="assignUbigeosTitle">Agregar Ubigeos</h2>
                <button class="modal-close" onclick="closeAssignUbigeosModal()" type="button">&times;</button>
            </div>

            <div class="modal-body">
                {{-- Buscador --}}
                <div class="ubigeo-search-wrapper">
                    <div class="ubigeo-search-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input 
                            type="text" 
                            class="ubigeo-search-input" 
                            id="ubigeoSearchInput" 
                            placeholder="Buscar por departamento, provincia o distrito..." 
                            onkeyup="searchUbigeos()"
                            autocomplete="off"
                        >
                    </div>
                </div>

                {{-- Lista de Ubigeos --}}
                <div class="ubigeos-list-container">
                    <div id="ubigeosList">
                        <div class="ubigeo-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <p>Escribe para buscar ubigeos...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignUbigeosModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmAssignUbigeos()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Asignar <span class="selected-count-badge" id="selectedUbigeosCount">0</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Reemplazar Geosegmentos --}}
<div class="modal" id="cloneGeosegmentosModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content clone-modal-content">
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon clone-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                        </svg>
                    </div>
                    <div>
                        <h2>Reemplazar Geosegmentos</h2>
                        <p class="modal-subtitle">Reemplaza los geosegmentos actuales con los de otras zonas</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeCloneGeosegmentosModal()" type="button">&times;</button>
            </div>

            <form id="cloneGeosegmentosForm" onsubmit="confirmCloneGeosegmentos(event)">
                <div id="cloneModalBody">
                    {{-- Panel Izquierdo --}}
                    <div id="cloneModalSidebar">
                        <div id="cloneSearchSection">
                            <label class="form-label">Buscar Zonas</label>
                            <div id="cloneSearchBox">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <input 
                                    type="text" 
                                    id="cloneZonaSearchInput" 
                                    placeholder="Buscar zona..." 
                                    onkeyup="searchZonasForClone()"
                                    autocomplete="off"
                                >
                            </div>
                            <small class="form-hint">Selecciona una o más zonas origen</small>
                        </div>
                        
                        <div id="cloneZonasList">
                            <div class="loading-spinner-small">
                                <div class="spinner-small"></div>
                                <span>Cargando zonas...</span>
                            </div>
                        </div>
                    </div>

                    {{-- Panel Derecho --}}
                    <div id="cloneModalPreview">
                        <div id="clonePreviewContainer">
                            <div id="clonePreviewHeader">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 16v-4"></path>
                                    <path d="M12 8h.01"></path>
                                </svg>
                                <span>Vista Previa del Reemplazo</span>
                            </div>
                            
                            <div id="cloneGeosegmentosPreview">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                                </svg>
                                <p>Selecciona zonas para ver la vista previa</p>
                            </div>
                            
                            <div id="cloneGeosegmentosPreviewContent">
                                <!-- Se llenará dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCloneGeosegmentosModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                        </svg>
                        Reemplazar Geosegmentos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Registrar Ausencia --}}
<div class="modal" id="ausenciaModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Registrar Ausencia</h2>
                <button class="modal-close" onclick="closeAusenciaModal()" type="button">&times;</button>
            </div>

            <form id="ausenciaForm" onsubmit="saveAusencia(event)">
                <div class="modal-body">
                    <input type="hidden" id="ausenciaIdFuerza">
                    <input type="hidden" id="ausenciaIdEmpleado">
                    
                    <div class="form-group">
                        <label class="form-label">Representante</label>
                        <input type="text" class="form-input" id="ausenciaNombreEmpleado" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipo de Ausencia <span class="required">*</span></label>
                        <select class="form-select" id="ausenciaIdTipoAusencia" required onchange="handleTipoAusenciaChange()">
                            <option value="">Seleccione un tipo de ausencia...</option>
                            @foreach(\DB::table('ODS.TAB_TIPO_AUSENCIA')->get() as $Tipo)
                                <option value="{{ $Tipo->idTipoAusencia }}" data-tipo="{{ strtolower($Tipo->Tipo) }}">{{ $Tipo->Tipo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha Inicio <span class="required">*</span></label>
                            <input type="date" class="form-input" id="ausenciaFechaInicio" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-input" id="ausenciaFechaFin">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observación</label>
                        <textarea class="form-input" id="ausenciaObservacion" rows="4" placeholder="Ingrese una observación (opcional)"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAusenciaModal()">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Registrar y Quitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Cambiar Empleado --}}
<div class="modal" id="cambiarEmpleadoModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="cambiarEmpleadoTitle">Cambiar Empleado</h2>
                <button class="modal-close" onclick="closeCambiarEmpleadoModal()" type="button">&times;</button>
            </div>

            <form id="cambiarEmpleadoForm" onsubmit="saveCambiarEmpleado(event)">
                <div class="modal-body">
                    <input type="hidden" id="cambiarIdAntiguo">
                    <input type="hidden" id="cambiarIdEmpleadoAntiguo">
                    <input type="hidden" id="cambiarTipo">
                    
                    <div class="form-group">
                        <label class="form-label">Empleado Actual</label>
                        <input type="text" class="form-input" id="cambiarNombreAntiguo" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nuevo Empleado <span class="required">*</span></label>
                        <input type="text" class="form-input" id="cambiarBuscarEmpleado" placeholder="Buscar empleado..." onkeyup="buscarEmpleadosParaCambio()" autocomplete="off">
                    </div>

                    <div class="empleados-list-container" id="cambiarEmpleadosListContainer" style="display: none;">
                        <div class="empleados-list" id="cambiarEmpleadosList"></div>
                    </div>

                    <input type="hidden" id="cambiarIdEmpleadoNuevo">
                    <div id="cambiarEmpleadoSeleccionado" style="display: none;" class="empleado-seleccionado">
                        <span id="cambiarEmpleadoSeleccionadoNombre"></span>
                        <button type="button" onclick="limpiarSeleccionEmpleado()" class="btn-limpiar">×</button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCambiarEmpleadoModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                        </svg>
                        Cambiar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Unificar Línea --}}
<div class="modal" id="unificarLineaModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon unify-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                        </svg>
                    </div>
                    <div>
                        <h2>Unificar Líneas</h2>
                        <p class="modal-subtitle">Combina dos líneas en una sola</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeUnificarLineaModal()" type="button">&times;</button>
            </div>

            <div class="unify-modal-body">
                <div class="unify-grid">
                    {{-- Columna Izquierda: Líneas Actuales --}}
                    <div class="unify-column">
                        <div class="unify-column-header">
                            <div class="unify-column-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>Líneas a Unificar</span>
                            </div>
                        </div>
                        <div class="unify-column-content">
                            <div class="unify-lineas-list" id="unifyLineasList">
                                <div class="loading-spinner">
                                    <div class="spinner"></div>
                                    <p>Cargando líneas...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Columna Derecha: Nueva Línea y Empleado --}}
                    <div class="unify-column">
                        <div class="unify-column-header">
                            <div class="unify-column-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                <span>Nueva Configuración</span>
                            </div>
                        </div>
                        <div class="unify-column-content">
                            {{-- Nueva Línea --}}
                            <div class="unify-section">
                                <label class="form-label">Nueva Línea (FranqLinea) <span class="required">*</span></label>
                                <select class="form-select" id="nuevaLineaSelect">
                                    <option value="">Seleccione una línea...</option>
                                </select>
                            </div>

                            {{-- Empleado para la Nueva Línea --}}
                            <div class="unify-section">
                                <label class="form-label">Empleado Asignado <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input" 
                                    id="unifyEmpleadoSearch" 
                                    placeholder="Buscar por nombre o código..."
                                    onkeyup="searchEmpleadosUnify()"
                                    autocomplete="off"
                                >
                                <div class="unify-empleado-list" id="unifyEmpleadoList">
                                    <div class="geo-empty">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                        <p>Escribe para buscar empleados...</p>
                                    </div>
                                </div>
                                <div class="unify-empleado-selected" id="unifyEmpleadoSelected" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUnificarLineaModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmUnificarLinea()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                    </svg>
                    Unificar Líneas
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
@vite('resources/css/clone-modal.css')
@endpush

@push('scripts')
@vite('resources/js/zonas.js')
@endpush
