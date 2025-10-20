@extends('layouts.app')

@section('title', 'Zonas - PharmaSales')

@section('content')
<div class="page-header">
    <h1 class="page-title">Gestión de Zonas</h1>
    <p class="page-subtitle">Administra zonas, asignaciones de empleados y geosegmentos</p>
</div>

<!-- Selector de Ciclo -->
<div class="ciclo-selector-container">
    <div class="ciclo-selector-box">
        <label for="cicloSelector" class="ciclo-label">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Ciclo
        </label>
        <select id="cicloSelector" class="ciclo-select" onchange="cambiarCiclo()">
            <option value="">Seleccionar ciclo...</option>
            @foreach($ciclos as $ciclo)
                <option value="{{ $ciclo->idCiclo }}" 
                    {{ request('ciclo') == $ciclo->idCiclo ? 'selected' : '' }}
                    data-estado="{{ $ciclo->estado->estado ?? '' }}">
                    {{ $ciclo->ciclo }} - {{ $ciclo->estado->estado ?? 'N/A' }}
                </option>
            @endforeach
        </select>
    </div>
    
    @if(request('ciclo'))
    <div class="ciclo-info-badge">
        <span class="ciclo-badge-label">Ciclo activo:</span>
        <span class="ciclo-badge-value">{{ $ciclos->firstWhere('idCiclo', request('ciclo'))->ciclo ?? 'N/A' }}</span>
    </div>
    @endif
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
            placeholder="Buscar zona..."
            value="{{ request('search') }}"
        >
    </div>
    <button class="btn btn-primary" onclick="abrirModalCrear()">
        <span class="btn-icon">+</span>
        Nueva Zona
    </button>
</div>

<div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Zona</th>
                <th>Estado</th>
                <th>Empleados Asignados</th>
                <th>Geosegmentos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($zonas as $zona)
            <tr>
                <td>{{ $zona->idZona }}</td>
                <td><strong>{{ $zona->zona }}</strong></td>
                <td>
                    <span class="badge {{ $zona->estado && $zona->estado->estado == 'Activo' ? 'badge-success' : 'badge-danger' }}">
                        {{ $zona->estado->estado ?? 'N/A' }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="count-badge">{{ $zona->zonasEmpleados->count() }}</span>
                </td>
                <td class="text-center">
                    <span class="count-badge">{{ $zona->zonasGeosegmentos->count() }}</span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon-action btn-view" onclick="verDetalle({{ $zona->idZona }})" title="Ver detalle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="btn-icon-action btn-edit" onclick="abrirModalEditar({{ $zona->idZona }})" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn-icon-action btn-delete" onclick="confirmarDesactivar({{ $zona->idZona }}, '{{ $zona->zona }}')" title="Desactivar">
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
                <td colspan="6" class="no-data">No hay zonas registradas</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
<div class="pagination-info">
    <div class="results-text">
        Mostrando {{ $zonas->firstItem() ?? 0 }} a {{ $zonas->lastItem() ?? 0 }} de {{ $zonas->total() }} resultados
    </div>
    <div class="pagination-controls">
        {{ $zonas->links() }}
    </div>
</div>

<!-- Modal Crear/Editar Zona -->
<div id="modalZona" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h2 id="modalTitle">Nueva Zona</h2>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
        </div>
        <form id="formZona" onsubmit="guardarZona(event)">
            <div class="modal-body">
                <input type="hidden" id="zonaId">
                
                <!-- Información Básica -->
                <div class="form-section">
                    <h3 class="form-section-title">Información Básica</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="zona" class="form-label">Nombre de Zona *</label>
                            <input type="text" id="zona" name="zona" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="idEstado" class="form-label">Estado *</label>
                            <select id="idEstado" name="idEstado" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->idEstado }}">{{ $estado->estado }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Asignación de Empleados -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Empleados Asignados</h3>
                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalAsignarEmpleado()">
                            <span class="btn-icon">+</span>
                            Asignar Empleado
                        </button>
                    </div>
                    <div id="listaEmpleadosAsignados" class="lista-asignaciones">
                        <p class="empty-message">No hay empleados asignados</p>
                    </div>
                </div>

                <!-- Asignación de Geosegmentos -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Geosegmentos Asignados</h3>
                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalAsignarGeosegmento()">
                            <span class="btn-icon">+</span>
                            Asignar Geosegmento
                        </button>
                    </div>
                    <div id="listaGeosegmentosAsignados" class="lista-asignaciones">
                        <p class="empty-message">No hay geosegmentos asignados</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Asignar Empleado -->
<div id="modalAsignarEmpleado" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Asignar Empleado</h2>
            <button class="modal-close" onclick="cerrarModalAsignarEmpleado()">&times;</button>
        </div>
        <form id="formAsignarEmpleado" onsubmit="agregarEmpleado(event)">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="empleadoSelect" class="form-label">Empleado *</label>
                        <select id="empleadoSelect" name="idEmpleado" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach($empleados as $empleado)
                                <option value="{{ $empleado->idEmpleado }}">{{ $empleado->apeNombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cicloEmpleado" class="form-label">Ciclo *</label>
                        <select id="cicloEmpleado" name="idCiclo" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->idCiclo }}">{{ $ciclo->ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalAsignarEmpleado()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Asignar Geosegmento -->
<div id="modalAsignarGeosegmento" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Asignar Geosegmento</h2>
            <button class="modal-close" onclick="cerrarModalAsignarGeosegmento()">&times;</button>
        </div>
        <form id="formAsignarGeosegmento" onsubmit="agregarGeosegmento(event)">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="geosegmentoSelect" class="form-label">Geosegmento *</label>
                        <select id="geosegmentoSelect" name="idGeosegmento" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach($geosegmentos as $geosegmento)
                                <option value="{{ $geosegmento->idGeosegmento }}">{{ $geosegmento->geosegmento }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cicloGeosegmento" class="form-label">Ciclo *</label>
                        <select id="cicloGeosegmento" name="idCiclo" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->idCiclo }}">{{ $ciclo->ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalAsignarGeosegmento()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ver Detalle de Zona -->
<div id="modalDetalle" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h2>Detalle de Zona</h2>
            <button class="modal-close" onclick="cerrarModalDetalle()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="detalle-zona-info">
                <h3 id="detalleZonaNombre"></h3>
                <span id="detalleZonaEstado" class="badge"></span>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="cambiarTab('empleados')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        Empleados Asignados
                    </button>
                    <button class="tab-btn" onclick="cambiarTab('geosegmentos')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                        Geosegmentos
                    </button>
                    <button class="tab-btn" onclick="cambiarTab('ubigeos')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Ubigeos
                    </button>
                </div>

                <!-- Tab Content: Empleados -->
                <div id="tabEmpleados" class="tab-content active">
                    <div class="tab-actions">
                        <button class="btn btn-primary btn-sm" onclick="abrirModalAsignarEmpleado()">
                            <span class="btn-icon">+</span>
                            Asignar Empleado
                        </button>
                    </div>
                    <div id="tablaEmpleados" class="tab-table"></div>
                </div>

                <!-- Tab Content: Geosegmentos -->
                <div id="tabGeosegmentos" class="tab-content">
                    <div class="tab-actions">
                        <button class="btn btn-primary btn-sm" onclick="abrirModalAsignarGeosegmento()">
                            <span class="btn-icon">+</span>
                            Asignar Geosegmento
                        </button>
                    </div>
                    <div id="tablaGeosegmentos" class="tab-table"></div>
                </div>

                <!-- Tab Content: Ubigeos -->
                <div id="tabUbigeos" class="tab-content">
                    <div class="tab-actions">
                        <input type="text" id="searchUbigeo" class="search-input-small" placeholder="Buscar ubigeo...">
                    </div>
                    <div id="tablaUbigeos" class="tab-table"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalDetalle()">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal Confirmación Desactivar -->
<div id="confirmModal" class="modal">
    <div class="modal-content modal-confirm">
        <div class="modal-header-confirm">
            <div class="modal-icon-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h2 class="modal-title-confirm">Confirmar Desactivación</h2>
            <button class="modal-close" onclick="cerrarModalConfirmar()">&times;</button>
        </div>

        <div class="modal-body-confirm">
            <p id="confirmMessage" class="confirm-text"></p>
        </div>

        <div class="modal-footer-confirm">
            <button type="button" class="btn btn-secondary btn-confirm" onclick="cerrarModalConfirmar()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Cancelar
            </button>
            <button type="button" class="btn btn-danger btn-confirm" onclick="desactivarZona()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                Desactivar
            </button>
        </div>
    </div>
</div>

<!-- Toast para notificaciones -->
<div id="toast" class="toast"></div>

<link rel="stylesheet" href="{{ asset('css/zonas.css') }}">
<script src="{{ asset('js/zonas.js') }}"></script>
@endsection

