@extends('layouts.app')

@section('title', 'Bricks - PharmaSales')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-text">
            <h1 class="page-title">Gestión de Bricks</h1>
            <p class="page-subtitle">Administra los bricks asignados a geosegmentos</p>
        </div>
        <button class="btn btn-primary" onclick="openReasignarBricksModal()" {{ !$cicloEsActivo ? 'disabled' : '' }} id="btnReasignarBricks">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="17 1 21 5 17 9"></polyline>
                <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                <polyline points="7 23 3 19 7 15"></polyline>
                <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
            </svg>
            <span>Reasignar Bricks</span>
        </button>
    </div>
</div>

{{-- Filters Section --}}
<div class="filters-section">
    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 2v20M2 12h20"></path>
            </svg>
            <span>Ciclo</span>
        </div>
        <select class="filter-input" id="cicloFilter" onchange="applyFilters()">
            @foreach($ciclos as $ciclo)
                <option value="{{ $ciclo->idCiclo }}" {{ $cicloSeleccionado == $ciclo->idCiclo ? 'selected' : '' }}>
                    {{ $ciclo->ciclo }} {{ $ciclo->esActivo ? '(Activo)' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <span>Departamento</span>
        </div>
        <select class="filter-input" id="departamentoFilter" onchange="applyFilters()">
            <option value="">Todos los departamentos</option>
            @foreach($departamentos as $departamento)
                <option value="{{ $departamento }}" {{ request('departamento') == $departamento ? 'selected' : '' }}>
                    {{ $departamento }}
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
        <input type="text" class="filter-input" id="searchInput" placeholder="Buscar brick, geosegmento..." value="{{ request('search') }}" onkeyup="searchBricks()">
    </div>
</div>

@if(!$cicloEsActivo && $cicloSeleccionado)
    <div class="alert-container">
        <div class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                <path d="M12 9v4"></path>
                <path d="M12 17h.01"></path>
            </svg>
            <span>Este ciclo está cerrado. No se pueden reasignar bricks en ciclos cerrados.</span>
        </div>
    </div>
@endif

{{-- Bricks Table --}}
<div class="zones-table-container">
    <div class="table-wrapper">
        <table class="zones-table" id="bricksTable">
            <thead>
                <tr>
                    <th>Brick</th>
                    <th>Geosegmento</th>
                    <th>Departamento</th>
                    <th>Provincia</th>
                    <th>Distrito</th>
                    <th>Ubigeos</th>
                    <th>Ciclo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="bricksTableBody">
                @forelse($bricks as $brick)
                    <tr>
                        <td>
                            <div class="zone-name">
                                <div class="zone-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    </svg>
                                </div>
                                <div class="brick-info">
                                    <span class="zone-text">{{ $brick->descripcionBrick }}</span>
                                    @if($brick->codigoBrick)
                                        <span class="brick-code">{{ $brick->codigoBrick }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-primary">{{ $brick->geosegmento }}</span>
                        </td>
                        <td>
                            @if($brick->departamento)
                                <div class="location-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span class="location-text">{{ $brick->departamento }}</span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($brick->provincia)
                                <div class="location-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="12" r="4"></circle>
                                    </svg>
                                    <span class="location-text">{{ $brick->provincia }}</span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($brick->distrito)
                                <div class="location-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                        <path d="M2 17l10 5 10-5"></path>
                                        <path d="M2 12l10 5 10-5"></path>
                                    </svg>
                                    <span class="location-text">{{ $brick->distrito }}</span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="count-badge {{ $brick->total_ubigeos == 0 ? 'count-badge-empty' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                <span>{{ $brick->total_ubigeos ?? 0 }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-ciclo">{{ $brick->ciclo }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ strtolower($brick->estado) }}">
                                <span class="status-dot"></span>
                                {{ $brick->estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <p>No hay bricks registrados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($bricks->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando {{ $bricks->firstItem() }} - {{ $bricks->lastItem() }} de {{ $bricks->total() }} bricks
            </div>
            <div class="pagination">
                {{ $bricks->links('vendor.pagination.custom') }}
            </div>
        </div>
    @endif
</div>

{{-- Modal Reasignar Bricks --}}
<div class="modal modal-bricks-reasign" id="reasignarBricksModal">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- Header --}}
            <div class="modal-header">
                <h2>Reasignar Bricks entre Geosegmentos</h2>
                <button class="modal-close" onclick="closeReasignarBricksModal()" type="button">&times;</button>
            </div>

            {{-- Body Horizontal --}}
            <div class="modal-body-horizontal">
                {{-- Panel Izquierdo: Selección --}}
                <div class="modal-sidebar">
                    <div class="sidebar-section">
                        <label class="form-label">
                            <span class="step-badge">1</span>
                            Geosegmento Origen
                        </label>
                        <select id="geosegmentoOrigenModal" class="form-input">
                            <option value="">Seleccionar...</option>
                        </select>
                        <small class="form-hint">Geosegmento que contiene los bricks</small>
                    </div>
                    
                    <div class="sidebar-section">
                        <label class="form-label">
                            <span class="step-badge">2</span>
                            Geosegmento Destino
                        </label>
                        <select id="geosegmentoDestinoModal" class="form-input">
                            <option value="">Seleccionar...</option>
                        </select>
                        <small class="form-hint">Geosegmento que recibirá los bricks</small>
                    </div>
                    
                    <div class="sidebar-section">
                        <div class="selection-summary">
                            <div class="summary-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="summary-content">
                                <div class="summary-count" id="selectedBricksCountModal">0</div>
                                <div class="summary-label">Bricks seleccionados</div>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <div class="info-box-small">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 16v-4"></path>
                                <path d="M12 8h.01"></path>
                            </svg>
                            <span>Si el origen se queda sin bricks, será desactivado automáticamente</span>
                        </div>
                    </div>
                </div>
                
                {{-- Panel Derecho: Lista de Bricks --}}
                <div class="modal-main-content">
                    <div class="bricks-list-header">
                        <label class="checkbox-label-inline">
                            <input type="checkbox" id="selectAllBricksModal" class="checkbox-input">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">Seleccionar todos</span>
                        </label>
                    </div>
                    <div class="bricks-list-container" id="bricksListModal">
                        <div class="brick-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <p>Selecciona un geosegmento origen para ver sus bricks</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeReasignarBricksModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmReasignarBricks()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="17 1 21 5 17 9"></polyline>
                        <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                        <polyline points="7 23 3 19 7 15"></polyline>
                        <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                    </svg>
                    Reasignar Bricks
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Confirmación --}}
<div class="modal" id="confirmModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="confirmModalTitle">Confirmar acción</h2>
                <button class="modal-close" onclick="closeConfirmModal()" type="button">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage" class="modal-description"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmModalButton">Confirmar</button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toast-container" class="toast-container"></div>

@endsection

@push('styles')
@vite(['resources/css/bricks.css', 'resources/css/bricks-reasignacion.css', 'resources/css/bricks-alerts.css'])
@endpush

@push('scripts')
@vite('resources/js/bricks-reasignacion.js')
@endpush
