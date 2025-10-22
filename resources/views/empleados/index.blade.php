@extends('layouts.app')

@section('title', 'Empleados - PharmaSales')

@section('content')
<div class="employees-container">
    {{-- Header Section --}}
    <header class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">Gestión de Empleados</h1>
                <p class="page-description">Administra tu equipo de ventas de forma eficiente</p>
            </div>
            <button class="btn btn-primary" onclick="openModal('create')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nuevo Empleado
            </button>
        </div>
    </header>

    {{-- Search and Filters --}}
    <div class="toolbar">
        <div class="search-wrapper">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <input 
                type="text" 
                class="search-input" 
                placeholder="Buscar por nombre, DNI o cargo..."
                id="searchInput"
                oninput="searchEmployees()"
            >
        </div>

        <div class="filters">
            <select class="filter-select" id="filterCargo" onchange="applyFilters()">
                <option value="">Todos los cargos</option>
                @foreach($cargos as $cargo)
                    <option value="{{ $cargo->idCargo }}">{{ $cargo->cargo }}</option>
                @endforeach
            </select>

            <select class="filter-select" id="filterArea" onchange="applyFilters()">
                <option value="">Todas las áreas</option>
                @foreach($areas as $area)
                    <option value="{{ $area->idArea }}">{{ $area->area }}</option>
                @endforeach
            </select>


            <button class="btn btn-secondary btn-icon" onclick="clearFilters()" title="Limpiar filtros">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>DNI</th>
                        <th>Cargo</th>
                        <th>Área</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="employeesTable">
                    @foreach($empleados as $empleado)
                    <tr data-id="{{ $empleado->idEmpleado }}">
                        <td>
                            <div class="employee-info">
                                <div>
                                    <div class="employee-name">{{ $empleado->nombre }}</div>
                                    <div class="employee-subtitle">{{ $empleado->apeNombre }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-light">{{ $empleado->dni }}</span></td>
                        <td>{{ $empleado->cargo->cargo ?? 'N/A' }}</td>
                        <td>{{ $empleado->area->area ?? 'N/A' }}</td>
                        <td>
                            @if($empleado->idEstado == 1)
                                <span class="status status-active">Activo</span>
                            @else
                                <span class="status status-inactive">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <button class="btn-action btn-action-edit" onclick="editEmployee({{ $empleado->idEmpleado }})" title="Editar">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                @if($empleado->idEstado == 1)
                                <button class="btn-action btn-action-delete" onclick="confirmDeactivate({{ $empleado->idEmpleado }}, '{{ $empleado->nombres }} {{ $empleado->apellidos }}')" title="Desactivar">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                        <path d="M10 11v6M14 11v6"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($empleados->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando {{ $empleados->firstItem() }} - {{ $empleados->lastItem() }} de {{ $empleados->total() }} empleados
            </div>
            <div class="pagination">
                {{ $empleados->links('vendor.pagination.custom') }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal Crear/Editar Empleado --}}
<div class="modal" id="employeeModal">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Nuevo Empleado</h2>
            <button class="modal-close" onclick="closeModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="employeeForm" onsubmit="saveEmployee(event)">
            <div class="modal-body">
                <input type="hidden" id="employeeId">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">DNI <span class="required">*</span></label>
                        <input type="text" class="form-input" id="dni" required maxlength="8" pattern="[0-9]{8}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nombre <span class="required">*</span></label>
                        <input type="text" class="form-input" id="nombre" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Apellido <span class="required">*</span></label>
                        <input type="text" class="form-input" id="apeNombre" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-input" id="correo">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cargo <span class="required">*</span></label>
                        <select class="form-select" id="idCargo" required>
                            <option value="">Seleccionar cargo</option>
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo->idCargo }}">{{ $cargo->cargo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Área <span class="required">*</span></label>
                        <select class="form-select" id="idArea" required>
                            <option value="">Seleccionar área</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->idArea }}">{{ $area->area }}</option>
                            @endforeach
                        </select>
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
            <button class="btn btn-danger" onclick="deactivateEmployee()">
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
@vite('resources/css/empleados.css')
@endpush

@push('scripts')
@vite('resources/js/empleados.js')
@endpush

