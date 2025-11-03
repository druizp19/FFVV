/* ============================================
   ZONAS MODULE - JavaScript
   ============================================ */

// ==========================================
// 1. STATE MANAGEMENT
// ==========================================
let currentZoneId = null;
let searchTimeout = null;
let pendingAction = null; // Para guardar la acción pendiente de confirmación

// ==========================================
// 2. MODAL MANAGEMENT
// ==========================================

/**
 * Abre el modal para crear o editar una zona
 */
function openModal(mode, zoneId = null) {
    // Verificar si el ciclo está cerrado
    if (window.isCicloCerrado && window.isCicloCerrado()) {
        showToast('No se pueden realizar modificaciones en un ciclo cerrado', 'warning');
        return;
    }
    
    const modal = document.getElementById('zoneModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('zoneForm');
    const estadoGroup = document.getElementById('estadoGroup');
    const estadoSelect = document.getElementById('idEstado');
    
    // Resetear formulario
    form.reset();
    document.getElementById('zoneId').value = '';
    
    if (mode === 'create') {
        modalTitle.textContent = 'Nueva Zona';
        // Ocultar selector de estado y establecer "Activo" por defecto
        estadoGroup.style.display = 'none';
        estadoSelect.removeAttribute('required');
        // Encontrar y seleccionar "Activo" (estado 1)
        const activeOption = Array.from(estadoSelect.options).find(opt => opt.value == '1');
        if (activeOption) {
            estadoSelect.value = activeOption.value;
        }
    } else if (mode === 'edit' && zoneId) {
        modalTitle.textContent = 'Editar Zona';
        // Mostrar selector de estado en modo edición
        estadoGroup.style.display = 'block';
        estadoSelect.setAttribute('required', 'required');
        currentZoneId = zoneId;
        loadZoneData(zoneId);
    }
    
    modal.classList.add('active');
    
    // Bloquear sidebar en modo responsive
    if (window.innerWidth <= 768 && window.blockSidebar) {
        window.blockSidebar();
    }
}

/**
 * Cierra el modal de crear/editar
 */
function closeModal() {
    const modal = document.getElementById('zoneModal');
    modal.classList.remove('active');
    currentZoneId = null;
    
    // Desbloquear sidebar
    if (window.unblockSidebar) {
        window.unblockSidebar();
    }
}

/**
 * Cierra el modal de detalles
 */
function closeDetailsModal() {
    const modal = document.getElementById('detailsModal');
    modal.classList.remove('active');
    currentZoneId = null; // Resetear aquí cuando se cierre el modal de detalles
    
    // Desbloquear sidebar
    if (window.unblockSidebar) {
        window.unblockSidebar();
    }
}

/**
 * Cierra el modal de confirmación
 */
function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
    // No resetear currentZoneId aquí porque se necesita para las actualizaciones en tiempo real
    
    // Desbloquear sidebar
    if (window.unblockSidebar) {
        window.unblockSidebar();
    }
}

// ==========================================
// 3. CRUD OPERATIONS
// ==========================================

/**
 * Carga los datos de una zona para editar
 */
async function loadZoneData(zoneId) {
    try {
        const response = await fetch(`/zonas/${zoneId}`);
        
        if (!response.ok) {
            throw new Error('Error al cargar los datos de la zona');
        }
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Error al cargar la zona');
        }
        
        const zone = result.data;
        
        // Llenar el formulario
        document.getElementById('zoneId').value = zone.idZona || '';
        document.getElementById('zona').value = zone.zona || '';
        document.getElementById('idEstado').value = zone.idEstado || '';
        
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message || 'Error al cargar la zona', 'error');
    }
}

/**
 * Guarda una zona (crear o actualizar)
 */
async function saveZone(event) {
    event.preventDefault();
    
    const zoneId = document.getElementById('zoneId').value;
    const zona = document.getElementById('zona').value;
    const idEstado = document.getElementById('idEstado').value;
    
    const data = {
        zona: zona,
        idEstado: parseInt(idEstado)
    };
    
    try {
        const url = zoneId ? `/zonas/${zoneId}` : '/zonas';
        const method = zoneId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(
                zoneId ? 'Zona actualizada exitosamente' : 'Zona creada exitosamente',
                'success'
            );
            closeModal();
            
            // Recargar la página después de 1 segundo
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(result.message || 'Error al guardar la zona', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar la zona', 'error');
    }
}

/**
 * Abre el modal para editar una zona
 */
function editZone(zoneId) {
    openModal('edit', zoneId);
}

/**
 * Muestra el modal de confirmación genérico
 */
function showConfirmModal(title, message, buttonText, action) {
    const modal = document.getElementById('confirmModal');
    const titleElement = document.getElementById('confirmTitle');
    const messageElement = document.getElementById('confirmMessage');
    const buttonTextElement = document.getElementById('confirmButtonText');
    
    titleElement.textContent = title;
    messageElement.textContent = message;
    buttonTextElement.textContent = buttonText;
    
    // Guardar la acción a ejecutar
    pendingAction = action;
    
    modal.classList.add('active');
    
    // Bloquear sidebar en modo responsive
    if (window.innerWidth <= 768 && window.blockSidebar) {
        window.blockSidebar();
    }
}

/**
 * Ejecuta la acción pendiente confirmada
 */
async function executeConfirmAction() {
    if (pendingAction && typeof pendingAction === 'function') {
        await pendingAction();
        pendingAction = null;
    }
    closeConfirmModal();
}

/**
 * Muestra el modal de confirmación para desactivar zona
 */
function confirmDeactivate(zoneId, zoneName) {
    currentZoneId = zoneId;
    showConfirmModal(
        'Confirmar Desactivación',
        `¿Estás seguro de que deseas desactivar la zona "${zoneName}"?`,
        'Desactivar',
        deactivateZone
    );
}

/**
 * Desactiva una zona
 */
async function deactivateZone() {
    if (!currentZoneId) return;
    
    try {
        const response = await fetch(`/zonas/${currentZoneId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Zona desactivada exitosamente', 'success');
            
            // Recargar la página después de 1 segundo
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(result.message || 'Error al desactivar la zona', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al desactivar la zona', 'error');
    }
}

/**
 * Muestra los detalles de una zona
 */
async function viewZoneDetails(zoneId) {
    // Guardar el ID de la zona actual
    currentZoneId = zoneId;
    
    const modal = document.getElementById('detailsModal');
    const detailsContent = document.getElementById('detailsContent');
    const detailsTitle = document.getElementById('detailsTitle');
    
    // Mostrar modal con spinner
    modal.classList.add('active');
    
    // Bloquear sidebar en modo responsive
    if (window.innerWidth <= 768 && window.blockSidebar) {
        window.blockSidebar();
    }
    
    detailsContent.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Cargando detalles...</p>
        </div>
    `;
    
    try {
        // Obtener ciclo seleccionado
        const cycleFilter = document.getElementById('cycleFilter');
        const selectedCycle = cycleFilter ? cycleFilter.value : '';
        
        // Cargar datos de la zona
        const zoneResponse = await fetch(`/zonas/${zoneId}`);
        if (!zoneResponse.ok) throw new Error('Error al cargar la zona');
        const zoneResult = await zoneResponse.json();
        const zone = zoneResult.data;
        
        // Actualizar título
        detailsTitle.textContent = `Detalles de ${zone.zona}`;
        
        // Cargar empleados asignados
        const empleadosUrl = `/zonas/${zoneId}/empleados${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        const empleadosResponse = await fetch(empleadosUrl);
        const empleadosResult = await empleadosResponse.json();
        const empleados = empleadosResult.data || [];
        
        // Cargar geosegmentos asignados
        const geosegmentosUrl = `/zonas/${zoneId}/geosegmentos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        const geosegmentosResponse = await fetch(geosegmentosUrl);
        const geosegmentosResult = await geosegmentosResponse.json();
        const geosegmentos = geosegmentosResult?.data?.activos || [];
            
        console.log('geosegmentosResult:', geosegmentosResult);

        // Cargar ubigeos
        const ubigeosUrl = `/zonas/${zoneId}/ubigeos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        const ubigeosResponse = await fetch(ubigeosUrl);
        const ubigeosResult = await ubigeosResponse.json();
        const ubigeos = ubigeosResult.data || [];
        
        // Renderizar contenido
        detailsContent.innerHTML = `
            <!-- Buscador de empleados (solo visible en la pestaña de empleados) -->
            <div id="empleadoSearchContainer" class="empleado-search-container" style="display: block;">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" id="empleadoSearchInput" placeholder="Buscar empleados..." onkeyup="searchEmpleados()">
                </div>
                <div id="empleadoSearchResults" class="search-results"></div>
            </div>
            
            <!-- Buscador de geosegmentos (solo visible en la pestaña de geosegmentos) -->
            <div id="geosegmentSearchContainer" class="geosegment-search-container" style="display: none;">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" id="geosegmentSearchInput" placeholder="Buscar geosegmentos..." onkeyup="searchGeosegments()">
                </div>
                <div id="geosegmentSearchResults" class="search-results"></div>
            </div>
            
            <div class="details-tabs">
                <button class="tab-button active" id="empleadosTab" onclick="switchTab('empleados')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Empleados (${empleados.length})
                </button>
                <button class="tab-button" id="geosegmentosTab" onclick="switchTab('geosegmentos')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Geosegmentos (${geosegmentos.length})
                </button>
                <button class="tab-button" id="ubigeosTab" onclick="switchTab('ubigeos')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                    </svg>
                    Ubigeos (${ubigeos.length})
                </button>
            </div>
            
            <div class="tab-content active" id="tabContent">
                ${renderEmpleadosTab(empleados)}
            </div>
        `;
        
    } catch (error) {
        console.error('Error:', error);
        detailsContent.innerHTML = `
            <div class="error-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                <h3>Error al cargar los detalles</h3>
                <p>${error.message}</p>
            </div>
        `;
    }
}

/**
 * Renderiza la pestaña de empleados
 */
function renderEmpleadosTab(empleados) {
    if (empleados.length === 0) {
        return `
            <div class="empty-tab">
                <p>No hay empleados asignados a esta zona</p>
            </div>
        `;
    }
    
    return `
        <div class="empleados-cards-container">
                    ${empleados.map(emp => `
                <div class="empleado-card">
                    <button class="empleado-card-remove" onclick="removeEmployeeFromZone(${emp.idZonaEmp})" title="Quitar empleado de la zona">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                    <div class="empleado-card-content">
                        <h4 class="empleado-card-name">${emp.empleado ? emp.empleado.nombre : 'N/A'}</h4>
                        <p class="empleado-card-cargo">${emp.empleado && emp.empleado.cargo ? emp.empleado.cargo.cargo : 'N/A'}</p>
                    </div>
                </div>
                    `).join('')}
        </div>
    `;
}

/**
 * Renderiza la pestaña de geosegmentos
 */
function renderGeosegmentosTab(data) {
    // Verificar si data tiene la nueva estructura (activos/inactivos) o la antigua
    let activos = [];
    let inactivos = [];
    
    if (data && data.activos !== undefined && data.inactivos !== undefined) {
        // Nueva estructura
        activos = data.activos || [];
        inactivos = data.inactivos || [];
    } else {
        // Estructura antigua - solo activos
        activos = data || [];
        inactivos = [];
    }
    
    if (activos.length === 0 && inactivos.length === 0) {
        return `
            <div class="empty-tab">
                <p>No hay geosegmentos asignados a esta zona</p>
            </div>
        `;
    }
    
    return `
        <div class="kanban-container">
            <div class="kanban-column">
                <div class="kanban-header">
                    <h3>Activos (${activos.length})</h3>
                </div>
                <div class="kanban-cards" id="activos-column" ondrop="drop(event)" ondragover="allowDrop(event)">
                    ${activos.map(geo => `
                        <div class="geo-card activo ${window.isCicloCerrado && window.isCicloCerrado() ? 'disabled' : ''}" 
                             id="geo-card-${geo.idZonaGeo}" 
                             draggable="${window.isCicloCerrado && window.isCicloCerrado() ? 'false' : 'true'}" 
                             ondragstart="drag(event)">
                            <div class="geo-card-name">${geo.geosegmento ? geo.geosegmento.geosegmento : 'N/A'}</div>
                            <button class="geo-card-remove" onclick="removeGeosegmentFromZone(${geo.idZonaGeo})" title="Quitar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 6L6 18M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="kanban-column">
                <div class="kanban-header">
                    <h3>Inactivos (${inactivos.length})</h3>
                </div>
                <div class="kanban-cards" id="inactivos-column" ondrop="drop(event)" ondragover="allowDrop(event)">
                    ${inactivos.map(geo => `
                        <div class="geo-card inactivo ${window.isCicloCerrado && window.isCicloCerrado() ? 'disabled' : ''}" 
                             id="geo-card-${geo.idZonaGeo}" 
                             draggable="${window.isCicloCerrado && window.isCicloCerrado() ? 'false' : 'true'}" 
                             ondragstart="drag(event)">
                            <div class="geo-card-name">${geo.geosegmento ? geo.geosegmento.geosegmento : 'N/A'}</div>
                            <button class="geo-card-restore" onclick="restoreGeosegmentFromZone(${geo.idZonaGeo})" title="Restaurar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                                    <path d="M21 3v5h-5"/>
                                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                                    <path d="M3 21v-5h5"/>
                                </svg>
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
}

/**
 * Renderiza la pestaña de ubigeos
 */
function renderUbigeosTab(ubigeos) {
    if (ubigeos.length === 0) {
        return `
            <div class="empty-tab">
                <p>No hay ubigeos relacionados con esta zona</p>
            </div>
        `;
    }
    
    return `
        <div class="details-table">
            <table>
                <thead>
                    <tr>
                        <th>Ubigeo</th>
                        <th>Departamento</th>
                        <th>Provincia</th>
                        <th>Distrito</th>
                        <th>Geosegmento</th>
                    </tr>
                </thead>
                <tbody>
                    ${ubigeos.map(ubi => `
                        <tr>
                            <td>${ubi.ubigeo || 'N/A'}</td>
                            <td>${ubi.departamento || 'N/A'}</td>
                            <td>${ubi.provincia || 'N/A'}</td>
                            <td>${ubi.distrito || 'N/A'}</td>
                            <td>${ubi.geosegmento ? ubi.geosegmento.geosegmento : 'N/A'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

/**
 * Cambia entre pestañas en el modal de detalles
 */
async function switchTab(tabName, event = null) {
    // Remover clase active de todos los botones
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    
    // Activar el botón seleccionado
    let button;
    if (event && event.target) {
        button = event.target.closest('.tab-button');
    } else {
        // Si no hay evento, buscar el botón por el tabName
        button = document.getElementById(tabName + 'Tab');
    }
    
    if (button) {
        button.classList.add('active');
    }
    
    // Obtener el contenedor de contenido
    const tabContent = document.getElementById('tabContent');
    if (!tabContent || !currentZoneId) return;
    
    try {
        const cicloFilter = document.getElementById('cycleFilter');
        const selectedCycle = cicloFilter ? cicloFilter.value : '';
        
        // Mostrar/ocultar buscadores según la pestaña
        const empleadoSearchContainer = document.getElementById('empleadoSearchContainer');
        const geosegmentSearchContainer = document.getElementById('geosegmentSearchContainer');
        
        if (empleadoSearchContainer) {
            if (tabName === 'empleados') {
                empleadoSearchContainer.style.display = 'block';
            } else {
                empleadoSearchContainer.style.display = 'none';
            }
        }
        
        if (geosegmentSearchContainer) {
            if (tabName === 'geosegmentos') {
                geosegmentSearchContainer.style.display = 'block';
            } else {
                geosegmentSearchContainer.style.display = 'none';
            }
        }
        
        // Cargar datos según la pestaña seleccionada
        if (tabName === 'empleados') {
            const url = `/zonas/${currentZoneId}/empleados${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
            const response = await fetch(url);
            const result = await response.json();
            tabContent.innerHTML = renderEmpleadosTab(result.data || []);
        } else if (tabName === 'geosegmentos') {
            const url = `/zonas/${currentZoneId}/geosegmentos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
            const response = await fetch(url);
            const result = await response.json();
            tabContent.innerHTML = renderGeosegmentosTab(result.data || {});
        } else if (tabName === 'ubigeos') {
            const url = `/zonas/${currentZoneId}/ubigeos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
            const response = await fetch(url);
            const result = await response.json();
            tabContent.innerHTML = renderUbigeosTab(result.data || []);
        }
    } catch (error) {
        console.error('Error al cambiar de pestaña:', error);
        tabContent.innerHTML = '<div class="error-state"><p>Error al cargar los datos</p></div>';
    }
}

// ==========================================
// 4. SEARCH & FILTERS
// ==========================================

/**
 * Busca zonas por nombre
 */
function searchZones() {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        const searchInput = document.getElementById('searchInput');
        const cycleFilter = document.getElementById('cycleFilter');
        
        const searchValue = searchInput.value;
        const cycleValue = cycleFilter.value;
        
        // Construir URL con parámetros
        const params = new URLSearchParams();
        if (searchValue) params.append('search', searchValue);
        if (cycleValue) params.append('ciclo', cycleValue);
        
        // Redirigir con los parámetros
        window.location.href = `/zonas${params.toString() ? '?' + params.toString() : ''}`;
    }, 500);
}

/**
 * Filtra por ciclo
 */
function filterByCycle() {
    const cycleFilter = document.getElementById('cycleFilter');
    const searchInput = document.getElementById('searchInput');
    const warningElement = document.getElementById('cycleClosedWarning');
    
    const cycleValue = cycleFilter.value;
    const searchValue = searchInput.value;
    
    // Mostrar/ocultar mensaje de advertencia
    if (cycleValue && window.isCicloCerrado && window.isCicloCerrado()) {
        warningElement.style.display = 'block';
    } else {
        warningElement.style.display = 'none';
    }
    
    // Construir URL con parámetros
    const params = new URLSearchParams();
    if (searchValue) params.append('search', searchValue);
    if (cycleValue) params.append('ciclo', cycleValue);
    
    // Redirigir con los parámetros
    window.location.href = `/zonas${params.toString() ? '?' + params.toString() : ''}`;
}

/**
 * Limpia todos los filtros
 */
function clearFilters() {
    window.location.href = '/zonas';
}

// ==========================================
// 5. TOAST NOTIFICATIONS
// ==========================================

/**
 * Muestra una notificación toast
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' 
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
    
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
            <p class="toast-title">${type === 'success' ? 'Éxito' : 'Error'}</p>
            <p class="toast-message">${message}</p>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// ==========================================
// 6. GEOSEGMENT ACTIONS
// ==========================================

/**
 * Muestra confirmación antes de quitar un geosegmento de una zona
 */
function removeGeosegmentFromZone(idZonaGeo) {
    // Verificar si el ciclo está cerrado
    if (window.isCicloCerrado && window.isCicloCerrado()) {
        showToast('No se pueden realizar modificaciones en un ciclo cerrado', 'warning');
        return;
    }
    
    // Obtener el nombre del geosegmento del card
    const card = document.getElementById(`geo-card-${idZonaGeo}`);
    const geosegmentoName = card ? card.querySelector('.geo-card-name').textContent : 'este geosegmento';
    
    showConfirmModal(
        'Confirmar Eliminación',
        `¿Estás seguro de que deseas quitar "${geosegmentoName}" de esta zona?`,
        'Quitar',
        () => executeRemoveGeosegment(idZonaGeo)
    );
}

/**
 * Ejecuta la desactivación del geosegmento (cambia estado a 0)
 */
async function executeRemoveGeosegment(idZonaGeo) {
    try {
        const response = await fetch(`/zonas/geosegmentos/${idZonaGeo}/deactivate`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar el Kanban inmediatamente
            await updateKanbanInRealTime();
            
            showToast('Geosegmento desasignado exitosamente', 'success');
        } else {
            showToast(result.message || 'Error al desasignar el geosegmento', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al desasignar el geosegmento', 'error');
    }
}

/**
 * Muestra confirmación antes de restaurar un geosegmento
 */
function restoreGeosegmentFromZone(idZonaGeo) {
    // Verificar si el ciclo está cerrado
    if (window.isCicloCerrado && window.isCicloCerrado()) {
        showToast('No se pueden realizar modificaciones en un ciclo cerrado', 'warning');
        return;
    }
    
    // Obtener el nombre del geosegmento del card
    const card = document.getElementById(`geo-card-${idZonaGeo}`);
    const geosegmentoName = card ? card.querySelector('.geo-card-name').textContent : 'este geosegmento';
    
    showConfirmModal(
        'Confirmar Restauración',
        `¿Estás seguro de que deseas restaurar "${geosegmentoName}" a esta zona?`,
        'Restaurar',
        () => executeRestoreGeosegment(idZonaGeo)
    );
}

/**
 * Ejecuta la restauración del geosegmento (cambia estado a 1)
 */
async function executeRestoreGeosegment(idZonaGeo) {
    try {
        const response = await fetch(`/zonas/geosegmentos/${idZonaGeo}/activate`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar el Kanban inmediatamente
            await updateKanbanInRealTime();
            
            showToast('Geosegmento restaurado exitosamente', 'success');
        } else {
            showToast(result.message || 'Error al restaurar el geosegmento', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al restaurar el geosegmento', 'error');
    }
}

// ==========================================
// 8. EMPLOYEE SEARCH
// ==========================================

/**
 * Busca empleados disponibles para agregar a la zona
 */
async function searchEmpleados() {
    const searchInput = document.getElementById('empleadoSearchInput');
    const resultsContainer = document.getElementById('empleadoSearchResults');
    
    if (!searchInput || !resultsContainer) return;
    
    const searchTerm = searchInput.value.trim();
    
    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        return;
    }
    
    try {
        // Buscar empleados disponibles
        const response = await fetch(`/empleados/search?q=${encodeURIComponent(searchTerm)}`);
        const result = await response.json();
        
        console.log('Resultado de búsqueda de empleados:', result);
        
        if (result.success && result.data && result.data.length > 0) {
            // Renderizar resultados
            resultsContainer.innerHTML = result.data.map(empleado => `
                <div class="search-result-item" onclick="selectEmpleado(${empleado.idEmpleado}, '${empleado.nombre} ${empleado.apeNombre}')">
                    <div class="result-info">
                        <h5>${empleado.nombre} ${empleado.apeNombre}</h5>
                        <p>${empleado.cargo ? empleado.cargo.cargo : 'Sin cargo'} - ${empleado.area ? empleado.area.area : 'Sin área'}</p>
                    </div>
                    <div class="result-action">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                    </div>
                </div>
            `).join('');
        } else {
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <p>No se encontraron empleados con "${searchTerm}"</p>
                </div>
            `;
        }
        
    } catch (error) {
        console.error('Error al buscar empleados:', error);
        resultsContainer.innerHTML = `
            <div class="error-results">
                <p>Error al buscar empleados</p>
            </div>
        `;
    }
}

/**
 * Selecciona un empleado para agregar a la zona
 */
function selectEmpleado(empleadoId, empleadoName) {
    showConfirmModal(
        'Agregar Empleado',
        `¿Estás seguro de que deseas agregar "${empleadoName}" a esta zona?`,
        'Agregar',
        () => addEmpleadoToZone(empleadoId, empleadoName)
    );
}

/**
 * Quita un empleado de la zona (desactiva la relación)
 */
function removeEmployeeFromZone(idZonaEmp) {
    // Buscar el empleado para obtener su nombre
    const empleadoButton = document.querySelector(`button[onclick="removeEmployeeFromZone(${idZonaEmp})"]`);
    if (!empleadoButton) return;
    
    // Buscar la tarjeta del empleado (puede ser .empleado-card o tr)
    const empleadoCard = empleadoButton.closest('.empleado-card');
    const empleadoRow = empleadoButton.closest('tr');
    
    let empleadoName = '';
    
    if (empleadoCard) {
        // Si es una tarjeta, obtener el nombre del h4
        const nameElement = empleadoCard.querySelector('.empleado-card-name');
        empleadoName = nameElement ? nameElement.textContent : 'empleado';
    } else if (empleadoRow) {
        // Si es una fila de tabla, obtener el nombre de la primera celda
        const nameCell = empleadoRow.querySelector('td:first-child');
        empleadoName = nameCell ? nameCell.textContent : 'empleado';
    } else {
        empleadoName = 'empleado';
    }
    
    showConfirmModal(
        'Quitar Empleado',
        `¿Estás seguro de que deseas quitar "${empleadoName}" de esta zona?`,
        'Quitar',
        () => executeRemoveEmployee(idZonaEmp)
    );
}

/**
 * Ejecuta la eliminación del empleado de la zona
 */
async function executeRemoveEmployee(idZonaEmp) {
    try {
        const response = await fetch(`/zonas/empleados/${idZonaEmp}/deactivate`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar la pestaña de empleados
            await switchTab('empleados');
            
            showToast('Empleado quitado exitosamente', 'success');
        } else {
            showToast(result.message || 'Error al quitar el empleado', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al quitar el empleado', 'error');
    }
}

/**
 * Agrega un empleado a la zona
 */
async function addEmpleadoToZone(empleadoId, empleadoName) {
    if (!currentZoneId) return;
    
    try {
        const cicloFilter = document.getElementById('cycleFilter');
        const selectedCycle = cicloFilter ? cicloFilter.value : '';
        
        if (!selectedCycle) {
            showToast('Debes seleccionar un ciclo para agregar empleados', 'warning');
            return;
        }
        
        const response = await fetch(`/zonas/${currentZoneId}/empleados`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                idEmpleado: empleadoId,
                idCiclo: selectedCycle
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Limpiar búsqueda
            const searchInput = document.getElementById('empleadoSearchInput');
            const resultsContainer = document.getElementById('empleadoSearchResults');
            if (searchInput) searchInput.value = '';
            if (resultsContainer) resultsContainer.innerHTML = '';
            
            // Actualizar la pestaña de empleados
            await switchTab('empleados');
            
            showToast('Empleado agregado exitosamente', 'success');
        } else {
            showToast(result.message || 'Error al agregar el empleado', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al agregar el empleado', 'error');
    }
}

// ==========================================
// 9. GEOSEGMENT SEARCH
// ==========================================

/**
 * Busca geosegmentos disponibles para agregar a la zona
 */
async function searchGeosegments() {
    const searchInput = document.getElementById('geosegmentSearchInput');
    const resultsContainer = document.getElementById('geosegmentSearchResults');
    
    if (!searchInput || !resultsContainer) return;
    
    const searchTerm = searchInput.value.trim();
    
    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        return;
    }
    
    try {
        // Buscar en los geosegmentos disponibles
        const availableGeosegments = window.geosegmentosData || [];
        const filtered = availableGeosegments.filter(geo => 
            geo.geosegmento.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        if (filtered.length === 0) {
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <p>No se encontraron geosegmentos con "${searchTerm}"</p>
                </div>
            `;
            return;
        }
        
        // Renderizar resultados
        resultsContainer.innerHTML = filtered.map(geo => `
            <div class="search-result-item" onclick="selectGeosegment(${geo.idGeosegmento}, '${geo.geosegmento}')">
                <div class="result-info">
                    <h5>${geo.geosegmento}</h5>
                    <p>${geo.lugar || 'Sin ubicación'}</p>
                </div>
                <div class="result-action">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('Error al buscar geosegmentos:', error);
        resultsContainer.innerHTML = `
            <div class="error-results">
                <p>Error al buscar geosegmentos</p>
            </div>
        `;
    }
}

/**
 * Selecciona un geosegmento para agregar a la zona
 */
function selectGeosegment(geosegmentId, geosegmentName) {
    showConfirmModal(
        'Agregar Geosegmento',
        `¿Estás seguro de que deseas agregar "${geosegmentName}" a esta zona?`,
        'Agregar',
        () => addGeosegmentToZone(geosegmentId, geosegmentName)
    );
}

/**
 * Agrega un geosegmento a la zona
 */
async function addGeosegmentToZone(geosegmentId, geosegmentName) {
    if (!currentZoneId) return;
    
    try {
        const cicloFilter = document.getElementById('cycleFilter');
        const selectedCycle = cicloFilter ? cicloFilter.value : '';
        
        if (!selectedCycle) {
            showToast('Debes seleccionar un ciclo para agregar geosegmentos', 'warning');
            return;
        }
        
        const response = await fetch(`/zonas/${currentZoneId}/geosegmentos`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                idGeosegmento: geosegmentId,
                idCiclo: selectedCycle
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Limpiar búsqueda
            const searchInput = document.getElementById('geosegmentSearchInput');
            const resultsContainer = document.getElementById('geosegmentSearchResults');
            if (searchInput) searchInput.value = '';
            if (resultsContainer) resultsContainer.innerHTML = '';
            
            // Actualizar el Kanban en tiempo real
            await updateKanbanInRealTime();
            
            showToast('Geosegmento agregado exitosamente', 'success');
        } else {
            showToast(result.message || 'Error al agregar el geosegmento', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al agregar el geosegmento', 'error');
    }
}

// ==========================================
// 9. KANBAN REAL-TIME UPDATE
// ==========================================

/**
 * Actualiza el Kanban en tiempo real sin recargar toda la modal
 */
async function updateKanbanInRealTime() {
    if (!currentZoneId) return;
    
    try {
        console.log('🔄 Actualizando Kanban en tiempo real...');
        
        const cicloFilter = document.getElementById('cycleFilter');
        const selectedCycle = cicloFilter ? cicloFilter.value : '';
        
        // Obtener los geosegmentos actualizados
        const geosegmentosUrl = `/zonas/${currentZoneId}/geosegmentos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        const response = await fetch(geosegmentosUrl);
        const result = await response.json();
        
        if (result.success) {
            // Actualizar solo la pestaña de geosegmentos
            const tabContent = document.getElementById('tabContent');
            const activeTab = document.querySelector('.tab-button.active');
            
            // Siempre actualizar el contenido de geosegmentos si existe
            if (tabContent) {
                // Si estamos en la pestaña de geosegmentos, actualizar el contenido
                if (activeTab && activeTab.id === 'geosegmentosTab') {
                    tabContent.innerHTML = renderGeosegmentosTab(result.data || {});
                    console.log('✅ Kanban actualizado en tiempo real');
                }
                // Si no estamos en la pestaña de geosegmentos, solo actualizar los contadores
                // pero mantener el contenido actual de la pestaña activa
            }
            
            // Actualizar contadores en las pestañas
            const activosCount = result.data.activos ? result.data.activos.length : 0;
            const inactivosCount = result.data.inactivos ? result.data.inactivos.length : 0;
            const totalCount = activosCount + inactivosCount;
            
            // Actualizar contador en la pestaña
            const geosegmentosTab = document.getElementById('geosegmentosTab');
            if (geosegmentosTab) {
                geosegmentosTab.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Geosegmentos (${totalCount})
                `;
            }
            
            // Actualizar contador en la tabla principal
            const geosegmentosCountElement = document.getElementById(`geosegmentos-count-${currentZoneId}`);
            if (geosegmentosCountElement) {
                geosegmentosCountElement.textContent = totalCount;
                console.log('✅ Tabla: Actualizado geosegmentos a', totalCount);
            }
        }
    } catch (error) {
        console.error('❌ Error al actualizar Kanban:', error);
    }
}

// ==========================================
// 9. DRAG AND DROP FUNCTIONS
// ==========================================

/**
 * Permite el drop en las columnas
 */
function allowDrop(ev) {
    ev.preventDefault();
}

/**
 * Inicia el drag de un elemento
 */
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    ev.target.style.opacity = "0.5";
}

/**
 * Maneja el drop de un elemento
 */
function drop(ev) {
    ev.preventDefault();
    
    // Verificar si el ciclo está cerrado
    if (window.isCicloCerrado && window.isCicloCerrado()) {
        showToast('No se pueden realizar modificaciones en un ciclo cerrado', 'warning');
        return;
    }
    
    const data = ev.dataTransfer.getData("text");
    const draggedElement = document.getElementById(data);
    const targetColumn = ev.currentTarget;
    
    // Restaurar opacidad del elemento arrastrado
    draggedElement.style.opacity = "1";
    
    // Determinar si es un cambio de estado
    const isFromActivos = draggedElement.closest('#activos-column');
    const isToInactivos = targetColumn.id === 'inactivos-column';
    const isFromInactivos = draggedElement.closest('#inactivos-column');
    const isToActivos = targetColumn.id === 'activos-column';
    
    // Si se mueve de activos a inactivos, mostrar confirmación
    if (isFromActivos && isToInactivos) {
        const idZonaGeo = data.replace('geo-card-', '');
        const geosegmentoName = draggedElement.querySelector('.geo-card-name').textContent;
        
        showConfirmModal(
            'Confirmar Desactivación',
            `¿Estás seguro de que deseas desactivar "${geosegmentoName}"?`,
            'Desactivar',
            () => executeRemoveGeosegment(idZonaGeo)
        );
        return;
    }
    
    // Si se mueve de inactivos a activos, mostrar confirmación
    if (isFromInactivos && isToActivos) {
        const idZonaGeo = data.replace('geo-card-', '');
        const geosegmentoName = draggedElement.querySelector('.geo-card-name').textContent;
        
        showConfirmModal(
            'Confirmar Restauración',
            `¿Estás seguro de que deseas restaurar "${geosegmentoName}"?`,
            'Restaurar',
            () => executeRestoreGeosegment(idZonaGeo)
        );
        return;
    }
    
    // Si no hay cambio de estado, solo mover visualmente
    targetColumn.appendChild(draggedElement);
}

/**
 * Recarga los detalles de la zona y actualiza los contadores en tiempo real
 */
async function refreshZoneDetails(zoneId) {
    try {
        console.log('🔄 Refrescando detalles de la zona:', zoneId);
        
        const cicloFilter = document.getElementById('cycleFilter');
        const selectedCycle = cicloFilter ? cicloFilter.value : '';
        
        // Obtener las asignaciones según el filtro de ciclo
        const empleadosUrl = `/zonas/${zoneId}/empleados${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        const geosegmentosUrl = `/zonas/${zoneId}/geosegmentos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        const ubigeosUrl = `/zonas/${zoneId}/ubigeos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        
        const [empleadosRes, geosegmentosRes, ubigeosRes] = await Promise.all([
            fetch(empleadosUrl),
            fetch(geosegmentosUrl),
            fetch(ubigeosUrl)
        ]);
        
        const empleadosData = await empleadosRes.json();
        const geosegmentosData = await geosegmentosRes.json();
        const ubigeosData = await ubigeosRes.json();
        
        const empleadosCount = empleadosData.data?.length || 0;
        const geosegmentosCount = geosegmentosData.data?.length || 0;
        const ubigeosCount = ubigeosData.data?.length || 0;
        
        console.log('📊 Nuevos contadores:', { empleadosCount, geosegmentosCount, ubigeosCount });
        
        // Actualizar los contadores en las pestañas del modal
        const empleadosTab = document.getElementById('empleadosTab');
        const geosegmentosTab = document.getElementById('geosegmentosTab');
        const ubigeosTab = document.getElementById('ubigeosTab');
        
        if (empleadosTab) {
            empleadosTab.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Empleados (${empleadosCount})
            `;
        }
        
        if (geosegmentosTab) {
            geosegmentosTab.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Geosegmentos (${geosegmentosCount})
            `;
        }
        
        if (ubigeosTab) {
            ubigeosTab.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                </svg>
                Ubigeos (${ubigeosCount})
            `;
        }
        
        // Actualizar los contadores en la tabla principal
        const empleadosCountElement = document.getElementById(`empleados-count-${zoneId}`);
        const geosegmentosCountElement = document.getElementById(`geosegmentos-count-${zoneId}`);
        const ubigeosCountElement = document.getElementById(`ubigeos-count-${zoneId}`);
        
        if (empleadosCountElement) {
            empleadosCountElement.textContent = empleadosCount;
            console.log('✅ Tabla: Actualizado empleados a', empleadosCount);
        }
        
        if (geosegmentosCountElement) {
            geosegmentosCountElement.textContent = geosegmentosCount;
            console.log('✅ Tabla: Actualizado geosegmentos a', geosegmentosCount);
        }
        
        if (ubigeosCountElement) {
            ubigeosCountElement.textContent = ubigeosCount;
            console.log('✅ Tabla: Actualizado ubigeos a', ubigeosCount);
        }
        
                // Actualizar el contenido de la pestaña activa en el modal
                const activeTab = document.querySelector('.tab-button.active');
                if (activeTab) {
                    const tabContent = document.getElementById('tabContent');
                    if (tabContent) {
                        const tabId = activeTab.id;
                        
                        if (tabId === 'empleadosTab') {
                            tabContent.innerHTML = renderEmpleadosTab(empleadosData.data || []);
                            console.log('🔄 Contenido actualizado: empleados');
                        } else if (tabId === 'geosegmentosTab') {
                            tabContent.innerHTML = renderGeosegmentosTab(geosegmentosData.data || {});
                            console.log('🔄 Contenido actualizado: geosegmentos');
                        } else if (tabId === 'ubigeosTab') {
                            tabContent.innerHTML = renderUbigeosTab(ubigeosData.data || []);
                            console.log('🔄 Contenido actualizado: ubigeos');
                        }
                    }
                }
        
        console.log('✅ Actualización completa finalizada');
    } catch (error) {
        console.error('❌ Error al refrescar detalles:', error);
    }
}

// ==========================================
// 7. GLOBAL SCOPE (Para Vite)
// ==========================================

// Exponer funciones al objeto window para que sean accesibles desde HTML
window.openModal = openModal;
window.closeModal = closeModal;
window.closeDetailsModal = closeDetailsModal;
window.closeConfirmModal = closeConfirmModal;
window.saveZone = saveZone;
window.editZone = editZone;
window.showConfirmModal = showConfirmModal;
window.executeConfirmAction = executeConfirmAction;
window.confirmDeactivate = confirmDeactivate;
window.deactivateZone = deactivateZone;
window.viewZoneDetails = viewZoneDetails;
window.searchZones = searchZones;
window.filterByCycle = filterByCycle;
window.clearFilters = clearFilters;
window.switchTab = switchTab;
window.showToast = showToast;
window.removeGeosegmentFromZone = removeGeosegmentFromZone;
window.restoreGeosegmentFromZone = restoreGeosegmentFromZone;
window.refreshZoneDetails = refreshZoneDetails;
window.updateKanbanInRealTime = updateKanbanInRealTime;
window.allowDrop = allowDrop;
window.drag = drag;
window.drop = drop;
window.searchEmpleados = searchEmpleados;
window.selectEmpleado = selectEmpleado;
window.addEmpleadoToZone = addEmpleadoToZone;
window.removeEmployeeFromZone = removeEmployeeFromZone;
window.executeRemoveEmployee = executeRemoveEmployee;
window.searchGeosegments = searchGeosegments;
window.selectGeosegment = selectGeosegment;
window.addGeosegmentToZone = addGeosegmentToZone;

// ==========================================
// 8. INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Zonas cargado correctamente');
    
    // Cerrar modales con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            closeDetailsModal();
            closeConfirmModal();
        }
    });
    
    // Inicializar mensaje de advertencia si hay un ciclo cerrado seleccionado
    if (window.cicloSeleccionado && window.isCicloCerrado && window.isCicloCerrado()) {
        const warningElement = document.getElementById('cycleClosedWarning');
        if (warningElement) {
            warningElement.style.display = 'block';
        }
    }
});

