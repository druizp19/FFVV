@extends('layouts.app')

@section('title', 'Ciclos Comerciales - PharmaSales')

@section('content')
<div class="page-header">
    <h1 class="page-title">Ciclos Comerciales</h1>
    <p class="page-subtitle">Gestiona los ciclos comerciales y su configuración</p>
</div>

<div class="actions-bar">
    <button class="btn btn-primary" onclick="openModal()">
        <span class="btn-icon">+</span>
        Nuevo Ciclo
    </button>
</div>

<div class="ciclos-grid">
    @forelse($ciclos as $ciclo)
        <div class="ciclo-card">
            <div class="ciclo-header">
                <div class="ciclo-info">
                    <h3 class="ciclo-codigo">{{ $ciclo->ciclo ?? $ciclo->codigo }}</h3>
                    <p class="ciclo-fechas">
                        {{ \Carbon\Carbon::parse($ciclo->fechaInicio)->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($ciclo->fechaFin)->format('d/m/Y') }}
                    </p>
                </div>
                <span class="badge badge-{{ $ciclo->estado === 'Abierto' ? 'success' : 'default' }}">
                    {{ $ciclo->estado }}
                </span>
            </div>

            <div class="ciclo-actions">
                <button class="btn-action" onclick="editarCiclo({{ $ciclo->idCiclo }})">
                    <span>✏️</span>
                    Editar
                </button>
                <button class="btn-action" onclick="copiarCiclo({{ $ciclo->idCiclo }})">
                    <span>📋</span>
                    Copiar
                </button>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <p>No hay ciclos registrados</p>
        </div>
    @endforelse
</div>

<!-- Modal Nuevo/Editar Ciclo -->
<div id="cicloModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo Ciclo</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>

        <p class="modal-description">Crea un nuevo ciclo comercial. Puedes copiar la configuración del ciclo anterior.</p>

        <form id="cicloForm" onsubmit="guardarCiclo(event)">
            <input type="hidden" id="cicloId" name="cicloId">
            <input type="hidden" id="copiarDatos" name="copiarDatos" value="true">

            <div class="form-group">
                <label for="nombreCiclo" class="form-label">Nombre del Ciclo</label>
                <input 
                    type="text" 
                    id="nombreCiclo" 
                    name="Ciclo" 
                    class="form-input" 
                    placeholder="Ej: Ciclo Octubre 2025"
                    required
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="fechaInicio" class="form-label">
                    Fecha de Inicio
                </label>
                <div class="date-input-wrapper">
                    <input 
                        type="text" 
                        id="fechaInicio" 
                        name="FechaInicio" 
                        class="form-input form-input-date"
                        placeholder="Seleccionar fecha"
                        required
                        readonly
                        onclick="openDatePicker('fechaInicio')"
                    >
                    <input 
                        type="date" 
                        id="fechaInicio_real" 
                        class="hidden-date-input"
                        required
                        onchange="updateDateDisplay('fechaInicio', this.value)"
                    >
                    <span class="calendar-icon" onclick="openDatePicker('fechaInicio')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="fechaFin" class="form-label">
                    Fecha de Fin
                </label>
                <div class="date-input-wrapper">
                    <input 
                        type="text" 
                        id="fechaFin" 
                        name="FechaFin" 
                        class="form-input form-input-date"
                        placeholder="Seleccionar fecha"
                        required
                        readonly
                        onclick="openDatePicker('fechaFin')"
                    >
                    <input 
                        type="date" 
                        id="fechaFin_real" 
                        class="hidden-date-input"
                        required
                        onchange="updateDateDisplay('fechaFin', this.value)"
                    >
                    <span class="calendar-icon" onclick="openDatePicker('fechaFin')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-group" id="copiarDatosContainer" style="margin-top: 24px;">
                <label class="checkbox-container">
                    <div class="checkbox-wrapper">
                        <input 
                            type="checkbox" 
                            id="copiarDatosCheck" 
                            class="form-checkbox"
                            checked
                            onchange="document.getElementById('copiarDatos').value = this.checked ? 'true' : 'false'"
                        >
                        <svg class="checkbox-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="checkbox-label">
                        <strong>Copiar datos del ciclo anterior</strong>
                        <small style="display: block; color: var(--text-tertiary); margin-top: 4px;">
                            Incluye: Productos, Zonas-Empleados, Zonas-Geosegmentos y Fuerza de Venta
                        </small>
                    </span>
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Contenedor de Toasts -->
<div id="toast-container" class="toast-container"></div>
@endsection
