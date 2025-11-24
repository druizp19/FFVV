@extends('layouts.app')

@section('title', 'FranqLinea - FFVV')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-text">
            <h1 class="page-title">Gestión de FranqLinea</h1>
            <p class="page-subtitle">Administra las combinaciones de Mixta, Línea y Estructura</p>
        </div>
        <button class="btn btn-primary" onclick="openCreateModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Nueva FranqLinea</span>
        </button>
    </div>
</div>

{{-- Filters Section --}}
<div class="filters-section">
    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <span>Buscar</span>
        </div>
        <input type="text" class="filter-input" id="searchInput" placeholder="Buscar por línea, mixta o estructura..." value="{{ request('search') }}" onkeyup="searchFranqLineas()">
    </div>
</div>

{{-- FranqLinea Table --}}
<div class="zones-table-container">
    <div class="table-wrapper">
        <table class="zones-table" id="franqlineasTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mixta</th>
                    <th>Estructura</th>
                    <th>Línea</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody id="franqlineasTableBody">
                @forelse($franqlineas as $fl)
                    <tr data-franqlinea-id="{{ $fl->idFranqLinea }}">
                        <td>
                            <div class="count-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                <span>{{ $fl->idFranqLinea }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="count-badge count-badge-supervisor">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>{{ $fl->mixta ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="count-badge count-badge-representante">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>{{ $fl->estructura ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="count-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>{{ $fl->linea ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ strtolower($fl->estado ?? 'inactivo') }}">
                                <span class="status-dot"></span>
                                {{ $fl->estado ?? 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <button class="action-btn action-edit" onclick="toggleEstado({{ $fl->idFranqLinea }}, '{{ $fl->estado }}')" title="Cambiar estado">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M12 16v-4"></path>
                                        <path d="M12 8h.01"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <p>No hay FranqLineas registradas</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($franqlineas->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando {{ $franqlineas->firstItem() }} - {{ $franqlineas->lastItem() }} de {{ $franqlineas->total() }} registros
            </div>
            <div class="pagination">
                {{ $franqlineas->links('vendor.pagination.custom') }}
            </div>
        </div>
    @endif
</div>

{{-- Modal Crear FranqLinea --}}
<div class="modal" id="createModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nueva FranqLinea</h2>
                <button class="modal-close" onclick="closeCreateModal()" type="button">&times;</button>
            </div>

            <form id="createForm" onsubmit="saveFranqLinea(event)">
                <div class="modal-body">
                    {{-- Columna Izquierda: Mixta --}}
                    <div class="form-group">
                        <label class="form-label">Mixta <span class="required">*</span></label>
                        
                        {{-- Selector de Mixta Existente --}}
                        <div class="select-or-create">
                            <select class="form-select" id="mixtaSelect" onchange="handleMixtaSelectChange()">
                                <option value="">Seleccione una mixta existente...</option>
                                <option value="__new__">+ Crear nueva mixta</option>
                            </select>
                        </div>

                        {{-- Input para Nueva Mixta (oculto por defecto) --}}
                        <div class="new-item-input" id="newMixtaInput" style="display: none;">
                            <div class="input-with-action">
                                <input type="text" class="form-input" id="newMixta" placeholder="Ej: Mixta Cardiovascular">
                                <button type="button" class="btn-add-item" onclick="createMixta()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Crear
                                </button>
                                <button type="button" class="btn-cancel-item" onclick="cancelNewMixta()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Lista de Mixtas Creadas --}}
                        <div class="created-items-list" id="mixtasList"></div>
                    </div>

                    {{-- Columna Derecha: Línea --}}
                    <div class="form-group">
                        <label class="form-label">Línea <span class="required">*</span></label>
                        
                        {{-- Selector de Línea Existente --}}
                        <div class="select-or-create">
                            <select class="form-select" id="lineaSelect" onchange="handleLineaSelectChange()">
                                <option value="">Seleccione una línea existente...</option>
                                <option value="__new__">+ Crear nueva línea</option>
                            </select>
                        </div>

                        {{-- Input para Nueva Línea (oculto por defecto) --}}
                        <div class="new-item-input" id="newLineaInput" style="display: none;">
                            <div class="input-with-action">
                                <input type="text" class="form-input" id="newLinea" placeholder="Ej: FARMA_A">
                                <button type="button" class="btn-add-item" onclick="createLinea()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Crear
                                </button>
                                <button type="button" class="btn-cancel-item" onclick="cancelNewLinea()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Lista de Líneas Creadas --}}
                        <div class="created-items-list" id="lineasList"></div>
                    </div>

                    {{-- Fila Completa: Estructura --}}
                    <div class="form-group">
                        <label class="form-label">Estructura <span class="required">*</span></label>
                        <select class="form-select" id="idEstructura" required>
                            <option value="">Seleccione una estructura...</option>
                            @foreach($estructuras as $e)
                                <option value="{{ $e->idEstructura }}">{{ $e->estructura }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar FranqLinea</button>
                </div>
            </form>
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

{{-- Toast Container --}}
<div id="toast-container" class="toast-container"></div>

@endsection

@push('styles')
@vite('resources/css/franqlinea.css')
@endpush

@push('scripts')
<script>
    window.estructurasData = @json($estructuras);
    window.estadosData = @json($estados);
</script>
@vite('resources/js/franqlinea.js')
@endpush
