@extends('layouts.app')

@section('title', 'Ciclos Comerciales - FFVV')

@section('content')
<div class="page-header">
    <div class="header-content">
        <div class="header-text">
            <h1 class="page-title">Ciclos Comerciales</h1>
            <p class="page-subtitle">Gestiona los ciclos comerciales y su configuración</p>
        </div>
        <button class="btn btn-primary" onclick="openModal()" id="btnNuevoCiclo" {{ $hayCicloAbierto ? 'disabled' : '' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Nuevo Ciclo</span>
        </button>
    </div>
</div>

@if($hayCicloAbierto)
    <div class="alert alert-warning" style="margin-bottom: 24px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <span>Hay un ciclo abierto actualmente. No se pueden crear o copiar ciclos hasta que se cierre.</span>
    </div>
@endif

<div class="ciclos-container">
    @forelse($ciclos as $ciclo)
        <div class="ciclo-card">
            <div class="ciclo-status">
                @php
                    $estadoCalculado = $ciclo->estado_calculado ?? 'Cerrado';
                @endphp
                <span class="status-badge status-{{ $estadoCalculado === 'Abierto' ? 'active' : 'closed' }}">
                    <span class="status-dot"></span>
                    {{ $estadoCalculado }}
                </span>
            </div>
            
            <div class="ciclo-content">
                <h3 class="ciclo-title">{{ $ciclo->ciclo ?? $ciclo->codigo }}</h3>
                
                <div class="ciclo-details">
                    <div class="detail-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($ciclo->fechaInicio)->format('d/m/Y') }}</span>
                    </div>
                    <div class="detail-separator">→</div>
                    <div class="detail-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($ciclo->fechaFin)->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="ciclo-meta">
                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>{{ $ciclo->diasHabiles ?? 0 }} días hábiles</span>
                    </div>
                </div>
            </div>

            <div class="ciclo-actions">
                <button class="action-btn action-edit" onclick="editarCiclo({{ $ciclo->idCiclo }})" title="Editar ciclo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Editar</span>
                </button>
                <button class="action-btn action-copy" onclick="copiarCiclo({{ $ciclo->idCiclo }})" title="Copiar ciclo" {{ $hayCicloAbierto ? 'disabled' : '' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span>Copiar</span>
                </button>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <h3>No hay ciclos registrados</h3>
            <p>Comienza creando tu primer ciclo comercial</p>
            <button class="btn btn-primary" onclick="openModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Crear Primer Ciclo</span>
            </button>
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

<script>
    window.hayCicloAbierto = {{ $hayCicloAbierto ? 'true' : 'false' }};
</script>
@endsection

@push('styles')
@vite('resources/css/ciclos.css')
@endpush

@push('scripts')
@vite('resources/js/ciclos.js')
@endpush
