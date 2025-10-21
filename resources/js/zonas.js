/* ============================================
   ZONAS MODULE - JavaScript
   ============================================ */

// ==========================================
// 1. STATE MANAGEMENT
// ==========================================
let currentZoneId = null;
let searchTimeout = null;

// ==========================================
// 2. MODAL MANAGEMENT
// ==========================================

/**
 * Abre el modal para crear o editar una zona
 */
function openModal(mode, zoneId = null) {
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
}

/**
 * Cierra el modal de crear/editar
 */
function closeModal() {
    const modal = document.getElementById('zoneModal');
    modal.classList.remove('active');
    currentZoneId = null;
}

/**
 * Cierra el modal de detalles
 */
function closeDetailsModal() {
    const modal = document.getElementById('detailsModal');
    modal.classList.remove('active');
}

/**
 * Cierra el modal de confirmación
 */
function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
    currentZoneId = null;
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
 * Muestra el modal de confirmación para desactivar
 */
function confirmDeactivate(zoneId, zoneName) {
    currentZoneId = zoneId;
    
    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    
    message.textContent = `¿Estás seguro de que deseas desactivar la zona "${zoneName}"?`;
    
    modal.classList.add('active');
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
            closeConfirmModal();
            
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
    const modal = document.getElementById('detailsModal');
    const detailsContent = document.getElementById('detailsContent');
    const detailsTitle = document.getElementById('detailsTitle');
    
    // Mostrar modal con spinner
    modal.classList.add('active');
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
        const geosegmentos = geosegmentosResult.data || [];
        
        // Cargar ubigeos
        const ubigeosUrl = `/zonas/${zoneId}/ubigeos${selectedCycle ? `?ciclo=${selectedCycle}` : ''}`;
        const ubigeosResponse = await fetch(ubigeosUrl);
        const ubigeosResult = await ubigeosResponse.json();
        const ubigeos = ubigeosResult.data || [];
        
        // Renderizar contenido
        detailsContent.innerHTML = `
            <div class="details-tabs">
                <button class="tab-button active" onclick="switchTab('empleados')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    Empleados (${empleados.length})
                </button>
                <button class="tab-button" onclick="switchTab('geosegmentos')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Geosegmentos (${geosegmentos.length})
                </button>
                <button class="tab-button" onclick="switchTab('ubigeos')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    </svg>
                    Ubigeos (${ubigeos.length})
                </button>
            </div>
            
            <div class="tab-content active" id="tab-empleados">
                ${renderEmpleadosTab(empleados)}
            </div>
            
            <div class="tab-content" id="tab-geosegmentos">
                ${renderGeosegmentosTab(geosegmentos)}
            </div>
            
            <div class="tab-content" id="tab-ubigeos">
                ${renderUbigeosTab(ubigeos)}
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
        <div class="details-table">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Cargo</th>
                        <th>Ciclo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    ${empleados.map(emp => `
                        <tr>
                            <td>${emp.empleado ? emp.empleado.nombre : 'N/A'}</td>
                            <td>${emp.empleado && emp.empleado.cargo ? emp.empleado.cargo.cargo : 'N/A'}</td>
                            <td>${emp.ciclo ? emp.ciclo.ciclo : 'N/A'}</td>
                            <td>
                                ${emp.estado && emp.estado.idEstado == 1 
                                    ? '<span class="status status-active">Activo</span>'
                                    : '<span class="status status-inactive">Inactivo</span>'
                                }
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

/**
 * Renderiza la pestaña de geosegmentos
 */
function renderGeosegmentosTab(geosegmentos) {
    if (geosegmentos.length === 0) {
        return `
            <div class="empty-tab">
                <p>No hay geosegmentos asignados a esta zona</p>
            </div>
        `;
    }
    
    return `
        <div class="details-table">
            <table>
                <thead>
                    <tr>
                        <th>Geosegmento</th>
                        <th>Lugar</th>
                        <th>Ciclo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    ${geosegmentos.map(geo => `
                        <tr>
                            <td>${geo.geosegmento ? geo.geosegmento.geosegmento : 'N/A'}</td>
                            <td>${geo.geosegmento ? geo.geosegmento.lugar : 'N/A'}</td>
                            <td>${geo.ciclo ? geo.ciclo.ciclo : 'N/A'}</td>
                            <td>
                                ${geo.estado && geo.estado.idEstado == 1 
                                    ? '<span class="status status-active">Activo</span>'
                                    : '<span class="status status-inactive">Inactivo</span>'
                                }
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
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
function switchTab(tabName) {
    // Remover clase active de todos los botones y contenidos
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Activar el botón y contenido seleccionado
    const button = event.target.closest('.tab-button');
    if (button) {
        button.classList.add('active');
    }
    
    const content = document.getElementById(`tab-${tabName}`);
    if (content) {
        content.classList.add('active');
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
    
    const cycleValue = cycleFilter.value;
    const searchValue = searchInput.value;
    
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
// 6. GLOBAL SCOPE (Para Vite)
// ==========================================

// Exponer funciones al objeto window para que sean accesibles desde HTML
window.openModal = openModal;
window.closeModal = closeModal;
window.closeDetailsModal = closeDetailsModal;
window.closeConfirmModal = closeConfirmModal;
window.saveZone = saveZone;
window.editZone = editZone;
window.confirmDeactivate = confirmDeactivate;
window.deactivateZone = deactivateZone;
window.viewZoneDetails = viewZoneDetails;
window.searchZones = searchZones;
window.filterByCycle = filterByCycle;
window.clearFilters = clearFilters;
window.switchTab = switchTab;
window.showToast = showToast;

// ==========================================
// 7. INITIALIZATION
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
});

