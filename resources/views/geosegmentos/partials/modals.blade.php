{{-- Modal Crear/Editar Geosegmento --}}
<div class="modal" id="geosegmentoModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="geosegmentoModalTitle">Nuevo Geosegmento</h2>
                <button class="modal-close" onclick="closeGeosegmentoModal()" type="button">&times;</button>
            </div>

            <form id="geosegmentoForm" onsubmit="saveGeosegmento(event)">
                <div class="modal-body">
                    <input type="hidden" id="geosegmentoId">

                    <div class="form-group">
                        <label class="form-label">Nombre del Geosegmento <span class="required">*</span></label>
                        <input type="text" class="form-input" id="geosegmentoNombre" required placeholder="Ej: LIMA CENTRO">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lugar</label>
                        <input type="text" class="form-input" id="geosegmentoLugar" placeholder="Ej: Lima, Perú">
                        <small class="form-hint">Ubicación geográfica del geosegmento (opcional)</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeGeosegmentoModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Asignar Ubigeos --}}
<div class="modal modal-ubigeos-assign" id="assignUbigeosModal">
    <div class="modal-dialog modal-horizontal-ubigeos">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="assignUbigeosTitle">Agregar Ubigeos</h2>
                <button class="modal-close" onclick="closeAssignUbigeosModal()" type="button">&times;</button>
            </div>

            <div class="modal-body-horizontal">
                {{-- Panel Izquierdo: Búsqueda y Resumen --}}
                <div class="modal-sidebar">
                    <div class="sidebar-section">
                        <label class="form-label">Buscar Ubigeos</label>
                        <div class="ubigeo-search-box">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <input 
                                type="text" 
                                class="ubigeo-search-input" 
                                id="ubigeoSearchInput" 
                                placeholder="Buscar..." 
                                onkeyup="searchUbigeos()"
                                autocomplete="off"
                            >
                        </div>
                        <small class="form-hint">Por departamento, provincia o distrito</small>
                    </div>
                    
                    <div class="sidebar-section">
                        <div class="selection-summary">
                            <div class="summary-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="summary-content">
                                <div class="summary-count" id="selectedUbigeosCount">0</div>
                                <div class="summary-label">Ubigeos seleccionados</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel Derecho: Lista de Ubigeos --}}
                <div class="modal-main-content">
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
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignUbigeosModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmAssignUbigeos()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Asignar Ubigeos
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ver Detalles --}}
<div class="modal" id="detailsModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="detailsTitle">Ubigeos del Geosegmento</h2>
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
