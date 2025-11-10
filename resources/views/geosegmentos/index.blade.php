@extends('layouts.app')

@section('title', 'Geosegmentos - PharmaSales')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-text">
            <h1 class="page-title">Gestión de Geosegmentos</h1>
            <p class="page-subtitle">Administra los geosegmentos y sus ubigeos asignados</p>
        </div>
        <button class="btn btn-primary" onclick="openCreateGeosegmentoModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Nuevo Geosegmento</span>
        </button>
    </div>
</div>

{{-- Filters Section --}}
<div class="filters-section">
    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>Filtrar</span>
        </div>
        <select class="filter-select" id="ubigeoFilter" onchange="filterGeosegmentos()">
            <option value="">Todos los geosegmentos</option>
            <option value="sin_ubigeos" {{ request('filter') == 'sin_ubigeos' ? 'selected' : '' }}>Sin ubigeos asignados</option>
            <option value="con_ubigeos" {{ request('filter') == 'con_ubigeos' ? 'selected' : '' }}>Con ubigeos asignados</option>
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
        <input type="text" class="filter-input" id="searchInput" placeholder="Buscar geosegmento..." value="{{ request('search') }}" onkeyup="searchGeosegmentos()">
    </div>
</div>

{{-- Geosegmentos Table --}}
<div class="zones-table-container">
    <div class="table-wrapper">
        <table class="zones-table" id="geosegmentosTable">
            <thead>
                <tr>
                    <th>Geosegmento</th>
                    <th>Lugar</th>
                    <th>Ubigeos</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody id="geosegmentosTableBody">
                @forelse($geosegmentos as $geo)
                    <tr data-geo-id="{{ $geo->idGeosegmento }}" data-geo-name="{{ $geo->geosegmento }}" data-ubigeos="{{ $geo->ubigeos_count }}">
                        <td>
                            <div class="zone-name">
                                <div class="zone-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <span class="zone-text">{{ $geo->geosegmento }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="lugar-text">{{ $geo->lugar ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="count-badge {{ $geo->ubigeos_count == 0 ? 'count-badge-empty' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                                <span>{{ $geo->ubigeos_count }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $estadoNombre = 'Inactivo';
                                if ($geo->estado && is_object($geo->estado)) {
                                    $estadoNombre = $geo->estado->estado ?? 'Inactivo';
                                }
                            @endphp
                            <span class="status-badge status-{{ strtolower($estadoNombre) }}">
                                <span class="status-dot"></span>
                                {{ $estadoNombre }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <button class="action-btn action-add" onclick="openAssignUbigeosModal({{ $geo->idGeosegmento }}, '{{ $geo->geosegmento }}')" title="Agregar/Reasignar ubigeos">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                                <button class="action-btn action-view" onclick="viewGeosegmentoDetails({{ $geo->idGeosegmento }})" title="Ver ubigeos">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="action-btn action-edit" onclick="openEditGeosegmentoModal({{ $geo->idGeosegmento }})" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <p>No hay geosegmentos registrados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($geosegmentos->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando {{ $geosegmentos->firstItem() }} - {{ $geosegmentos->lastItem() }} de {{ $geosegmentos->total() }} geosegmentos
            </div>
            <div class="pagination">
                {{ $geosegmentos->links('vendor.pagination.custom') }}
            </div>
        </div>
    @endif
</div>

{{-- Incluir modales desde zonas (reutilizables) --}}
@include('geosegmentos.partials.modals')

{{-- Toast Container --}}
<div id="toast-container" class="toast-container"></div>

{{-- Scripts --}}
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>
@endsection

@push('styles')
@vite('resources/css/zonas.css')
@endpush

@push('scripts')
@vite('resources/js/geosegmentos.js')
@endpush
