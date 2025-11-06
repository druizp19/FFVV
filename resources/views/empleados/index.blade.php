@extends('layouts.app')

@section('title', 'Empleados - PharmaSales')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-text">
            <h1 class="page-title">Empleados</h1>
            <p class="page-subtitle">Consulta la información de tu equipo de ventas</p>
        </div>
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
        <input type="text" class="filter-input" id="searchInput" placeholder="Buscar por nombre o DNI..." onkeyup="searchEmployees()">
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            <span>Cargo</span>
        </div>
        <select class="filter-select" id="filterCargo" onchange="applyFilters()">
            <option value="">Todos los cargos</option>
            @foreach($cargos as $cargo)
                <option value="{{ $cargo->idCargo }}" {{ request('cargo') == $cargo->idCargo ? 'selected' : '' }}>
                    {{ $cargo->cargo }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Área</span>
        </div>
        <select class="filter-select" id="filterArea" onchange="applyFilters()">
            <option value="">Todas las áreas</option>
            @foreach($areas as $area)
                <option value="{{ $area->idArea }}" {{ request('area') == $area->idArea ? 'selected' : '' }}>
                    {{ $area->area }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- Employees Table --}}
<div class="employees-table-container">
    <div class="table-wrapper">
        <table class="employees-table" id="employeesTable">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>DNI</th>
                    <th>Cargo</th>
                    <th>Área</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="employeesTableBody">
                @forelse($empleados as $empleado)
                    <tr data-employee-id="{{ $empleado->idEmpleado }}" data-employee-name="{{ $empleado->nombre }} {{ $empleado->apeNombre }}" data-employee-dni="{{ $empleado->dni }}">
                        <td>
                            <div class="employee-name-cell">
                                <div class="employee-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="employee-info">
                                    <span class="employee-name">{{ $empleado->nombre }}</span>
                                    <span class="employee-lastname">{{ $empleado->apeNombre }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="employee-dni">{{ $empleado->dni }}</span>
                        </td>
                        <td>
                            <span class="employee-cargo">{{ $empleado->cargo->cargo ?? 'Sin cargo' }}</span>
                        </td>
                        <td>
                            <span class="employee-area">{{ $empleado->area->area ?? 'Sin área' }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ strtolower($empleado->estado->estado ?? 'inactivo') }}">
                                <span class="status-dot"></span>
                                {{ $empleado->estado->estado ?? 'Inactivo' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <p>No hay empleados registrados</p>
                        </td>
                    </tr>
                @endforelse
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

{{-- Toast Container --}}
<div id="toast-container" class="toast-container"></div>
@endsection

@push('styles')
@vite('resources/css/empleados.css')
@endpush

@push('scripts')
@vite('resources/js/empleados.js')
@endpush
