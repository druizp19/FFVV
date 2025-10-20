@extends('layouts.app')

@section('title', 'Empleados - PharmaSales')

@section('content')
<div class="page-header">
    <h1 class="page-title">Gestión de Empleados</h1>
    <p class="page-subtitle">Administra los empleados y sus cargos</p>
</div>

<div class="actions-bar">
    <div class="search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input 
            type="text" 
            id="searchInput" 
            name="search"
            class="search-input" 
            placeholder="Buscar por nombre, apellido o DNI..."
            value="{{ request('search') }}"
        >
    </div>
    <button class="btn btn-primary" onclick="abrirModalCrear()">
        <span class="btn-icon">+</span>
        Nuevo Empleado
    </button>
</div>

<!-- Filtros -->
<div class="filters-bar">
    <form id="filtersForm" method="GET" action="{{ route('empleados.index') }}" class="filters-form">
        <input type="hidden" name="search" id="searchHidden" value="{{ request('search') }}">
        
        <div class="filter-group">
            <label for="filterCargo" class="filter-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                Cargo
            </label>
            <select id="filterCargo" name="cargo" class="filter-select" onchange="aplicarFiltros()">
                <option value="">Todos</option>
                @foreach($cargos as $cargo)
                    <option value="{{ $cargo->idCargo }}" {{ request('cargo') == $cargo->idCargo ? 'selected' : '' }}>
                        {{ $cargo->cargo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="filterArea" class="filter-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Área
            </label>
            <select id="filterArea" name="area" class="filter-select" onchange="aplicarFiltros()">
                <option value="">Todas</option>
                @foreach($areas as $area)
                    <option value="{{ $area->idArea }}" {{ request('area') == $area->idArea ? 'selected' : '' }}>
                        {{ $area->area }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="filterUne" class="filter-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                UNE
            </label>
            <select id="filterUne" name="une" class="filter-select" onchange="aplicarFiltros()">
                <option value="">Todas</option>
                @foreach($unes as $une)
                    <option value="{{ $une->idUne }}" {{ request('une') == $une->idUne ? 'selected' : '' }}>
                        {{ $une->unidadNegocio }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="button" class="btn-clear-filters" onclick="limpiarFiltros()" title="Limpiar filtros">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
            Limpiar
        </button>
    </form>
</div>

<div class="table-container">
    <table class="data-table" id="empleadosTable">
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre y Apellido</th>
                <th>Cargo</th>
                <th>Área</th>
                <th>UNE</th>
                <th>Correo</th>
                <th>Celular</th>
                <th>Fecha Ingreso</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empleados as $empleado)
                <tr data-empleado-id="{{ $empleado->idEmpleado }}">
                    <td class="text-center">{{ $empleado->dni }}</td>
                    <td>{{ $empleado->apeNombre }}</td>
                    <td>{{ $empleado->cargo->cargo ?? '-' }}</td>
                    <td>{{ $empleado->area->area ?? '-' }}</td>
                    <td>{{ $empleado->une->unidadNegocio ?? '-' }}</td>
                    <td>{{ $empleado->correo }}</td>
                    <td>{{ $empleado->celular ?? '-' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($empleado->fechaIngreso)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $empleado->estado->estado === 'Activo' ? 'success' : 'default' }}">
                            {{ $empleado->estado->estado ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button 
                                class="btn-icon-action btn-edit" 
                                onclick="abrirModalEditar({{ $empleado->idEmpleado }})"
                                title="Editar"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </button>
                            <button 
                                class="btn-icon-action btn-delete" 
                                onclick="confirmarDesactivar({{ $empleado->idEmpleado }}, '{{ $empleado->apeNombre }}')"
                                title="Desactivar"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center empty-state">No hay empleados registrados</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
<div class="pagination-info">
    <p class="results-text">
        Mostrando {{ $empleados->firstItem() ?? 0 }} - {{ $empleados->lastItem() ?? 0 }} de {{ $empleados->total() }} empleados
    </p>
    <div class="pagination-controls">
        {{ $empleados->links() }}
    </div>
</div>

<!-- Modal Crear/Editar Empleado -->
<div id="empleadoModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo Empleado</h2>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
        </div>

        <form id="empleadoForm" onsubmit="guardarEmpleado(event)">
            <input type="hidden" id="empleadoId" name="empleadoId">

            <div class="form-row">
                <div class="form-group">
                    <label for="dni" class="form-label">DNI <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="dni" 
                        name="dni" 
                        class="form-input" 
                        placeholder="12345678"
                        required
                        maxlength="8"
                        pattern="[0-9]{8}"
                    >
                </div>

                <div class="form-group">
                    <label for="nombre" class="form-label">Nombres <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        class="form-input" 
                        placeholder="Juan Carlos"
                        required
                        maxlength="100"
                    >
                </div>

                <div class="form-group">
                    <label for="apeNombre" class="form-label">Apellidos <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="apeNombre" 
                        name="apeNombre" 
                        class="form-input" 
                        placeholder="Pérez García"
                        required
                        maxlength="100"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="correo" class="form-label">Correo Electrónico <span class="required">*</span></label>
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        class="form-input" 
                        placeholder="juan.perez@empresa.com"
                        required
                        maxlength="100"
                    >
                </div>

                <div class="form-group">
                    <label for="celular" class="form-label">Celular</label>
                    <input 
                        type="text" 
                        id="celular" 
                        name="celular" 
                        class="form-input" 
                        placeholder="999888777"
                        maxlength="20"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="idCargo" class="form-label">Cargo <span class="required">*</span></label>
                    <select id="idCargo" name="idCargo" class="form-select" required>
                        <option value="">Seleccionar cargo</option>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->idCargo }}">{{ $cargo->cargo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="idArea" class="form-label">Área <span class="required">*</span></label>
                    <select id="idArea" name="idArea" class="form-select" required>
                        <option value="">Seleccionar área</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->idArea }}">{{ $area->area }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="idUne" class="form-label">UNE <span class="required">*</span></label>
                    <select id="idUne" name="idUne" class="form-select" required>
                        <option value="">Seleccionar UNE</option>
                        @foreach($unes as $une)
                            <option value="{{ $une->idUne }}">{{ $une->unidadNegocio }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fechaIngreso" class="form-label">Fecha de Ingreso <span class="required">*</span></label>
                    <input 
                        type="date" 
                        id="fechaIngreso" 
                        name="fechaIngreso" 
                        class="form-input" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="fechaCese" class="form-label">Fecha de Cese</label>
                    <input 
                        type="date" 
                        id="fechaCese" 
                        name="fechaCese" 
                        class="form-input"
                    >
                </div>

                <div class="form-group">
                    <label for="idEstado" class="form-label">Estado <span class="required">*</span></label>
                    <select id="idEstado" name="idEstado" class="form-select" required>
                        @foreach($estados as $estado)
                            <option value="{{ $estado->idEstado }}" {{ $estado->estado === 'Activo' ? 'selected' : '' }}>
                                {{ $estado->estado }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmación Desactivar -->
<div id="confirmModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2>Confirmar Desactivación</h2>
            <button class="modal-close" onclick="cerrarModalConfirmar()">&times;</button>
        </div>

        <div class="modal-body">
            <p id="confirmMessage"></p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalConfirmar()">Cancelar</button>
            <button type="button" class="btn btn-danger" onclick="desactivarEmpleado()">Desactivar</button>
        </div>
    </div>
</div>

<!-- Contenedor de Toasts -->
<div id="toast-container" class="toast-container"></div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/empleados.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/empleados.js') }}"></script>
@endpush

