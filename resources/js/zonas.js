/**
 * ============================================
 * Zonas Module - JavaScript
 * ============================================
 */

// ==========================================
// 1. STATE MANAGEMENT
// ==========================================
let currentZoneId = null;
let searchTimeout = null;
let pendingAction = null;
let cicloSeleccionado = null;

// Obtener CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ==========================================
// 2. TOAST NOTIFICATIONS
// ==========================================

const toastIcons = {
    success: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>`,
    error: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
        <line x1="12" y1="9" x2="12" y2="13"></line>
        <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>`,
    info: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
    </svg>`
};

const toastTitles = {
    success: '¡Éxito!',
    error: 'Error',
    warning: 'Advertencia',
    info: 'Información'
};

window.showToast = function (message, type = 'info', title = null, duration = 5000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const toastId = 'toast_' + Date.now();
    toast.id = toastId;

    toast.innerHTML = `
        <div class="toast-icon">${toastIcons[type]}</div>
        <div class="toast-content">
            <div class="toast-title">${title || toastTitles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast('${toastId}')">&times;</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        closeToast(toastId);
    }, duration);
}

window.closeToast = function (toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }
}

/**
 * Muestra un toast con un botón de acción
 */
window.showToastWithAction = function (message, type = 'success', actionText = 'Acción', buttonText = 'Aceptar', onAction = null, duration = 8000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type} toast-with-action`;
    const toastId = 'toast_' + Date.now();
    toast.id = toastId;

    toast.innerHTML = `
        <div class="toast-icon">${toastIcons[type]}</div>
        <div class="toast-content">
            <div class="toast-title">${toastTitles[type]}</div>
            <div class="toast-message">${message}</div>
            <div class="toast-action-text">${actionText}</div>
        </div>
        <div class="toast-actions">
            <button class="toast-action-btn" onclick="handleToastAction('${toastId}', ${onAction ? 'window.toastActions[\'' + toastId + '\']' : 'null'})">
                ${buttonText}
            </button>
            <button class="toast-close-btn" onclick="closeToast('${toastId}')">&times;</button>
        </div>
    `;

    // Guardar la función de acción
    if (onAction) {
        if (!window.toastActions) window.toastActions = {};
        window.toastActions[toastId] = onAction;
    }

    container.appendChild(toast);

    setTimeout(() => {
        closeToast(toastId);
        if (window.toastActions && window.toastActions[toastId]) {
            delete window.toastActions[toastId];
        }
    }, duration);
}

window.handleToastAction = function (toastId, actionFn) {
    if (actionFn && typeof actionFn === 'function') {
        actionFn();
    }
    closeToast(toastId);
}

// ==========================================
// 3. MODAL MANAGEMENT
// ==========================================

window.openModal = function (mode, zoneId = null) {
    if (isCicloCerrado()) {
        showToast('No se pueden realizar modificaciones en un ciclo cerrado', 'warning');
        return;
    }

    const modal = document.getElementById('zoneModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('zoneForm');
    const estadoGroup = document.getElementById('estadoGroup');

    form.reset();

    if (mode === 'create') {
        modalTitle.textContent = 'Nueva Zona';
        estadoGroup.style.display = 'none';
        currentZoneId = null;
    } else if (mode === 'edit' && zoneId) {
        modalTitle.textContent = 'Editar Zona';
        estadoGroup.style.display = 'block';
        currentZoneId = zoneId;
        loadZoneData(zoneId);
    }

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeModal = function () {
    const modal = document.getElementById('zoneModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        currentZoneId = null;
    }, 300);
}

window.closeDetailsModal = function () {
    const modal = document.getElementById('detailsModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        currentZoneId = null;
    }, 300);
}

window.closeConfirmModal = function () {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

// =========================================
// 4. ZONE CRUD OPERATIONS
// ==========================================

async function loadZoneData(zoneId) {
    try {
        const response = await fetch(`/zonas/${zoneId}`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            const zona = result.data;
            document.getElementById('zona').value = zona.zona;
            document.getElementById('idEstado').value = zona.idEstado;
        } else {
            showToast('No se pudo cargar la zona', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar los datos', 'error');
    }
}

window.saveZone = async function (event) {
    event.preventDefault();

    const zona = document.getElementById('zona').value;
    const idEstado = document.getElementById('idEstado').value;

    const data = {
        zona: zona,
        idEstado: currentZoneId ? idEstado : 1 // Activo por defecto al crear
    };

    const url = currentZoneId ? `/zonas/${currentZoneId}` : '/zonas';
    const method = currentZoneId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            closeModal();
            // Recargar la tabla sin refrescar la página
            await reloadZonesTable();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar la zona', 'error');
    }
}

window.confirmDeactivate = function (zoneId, zoneName) {
    if (isCicloCerrado()) {
        showToast('No se pueden realizar modificaciones en un ciclo cerrado', 'warning');
        return;
    }

    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');

    title.textContent = 'Confirmar Desactivación';
    message.textContent = `¿Estás seguro de que deseas desactivar la zona "${zoneName}"?`;
    buttonText.textContent = 'Desactivar';

    pendingAction = {
        type: 'deactivate',
        zoneId: zoneId
    };

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.executeConfirmAction = async function () {
    if (!pendingAction) return;

    if (pendingAction.type === 'deactivate') {
        await deactivateZone(pendingAction.zoneId);
    } else if (pendingAction.type === 'addGeosegmentos') {
        await addMultipleGeosegmentosToZone(pendingAction.geoIds);
    } else if (pendingAction.type === 'removeGeosegmento') {
        await removeGeosegmentoFromZone(pendingAction.geoId);
    } else if (pendingAction.type === 'addEmpleados') {
        await addMultipleEmpleadosToZone(pendingAction.empIds);
    } else if (pendingAction.type === 'removeEmpleado') {
        await removeEmpleadoFromZone(pendingAction.empId);
    }

    closeConfirmModal();
    pendingAction = null;
}

async function deactivateZone(zoneId) {
    try {
        const response = await fetch(`/zonas/${zoneId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            // Recargar la tabla sin refrescar la página
            await reloadZonesTable();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al desactivar la zona', 'error');
    }
}

// ==========================================
// 5. VIEW ZONE DETAILS
// ==========================================

window.viewZoneDetails = async function (zoneId) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('detailsContent');
    const title = document.getElementById('detailsTitle');

    currentZoneId = zoneId;

    // Mostrar spinner
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Cargando detalles...</p>
        </div>
    `;

    modal.classList.remove('closing');
    modal.classList.add('active');

    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;

        const url = cicloId
            ? `/zonas/${zoneId}/detalles?ciclo=${cicloId}`
            : `/zonas/${zoneId}/detalles`;

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            const zona = result.zona;
            title.textContent = `Detalles: ${zona.zona}`;
            content.innerHTML = buildDetailsHTML(zona);
        } else {
            content.innerHTML = `<p class="error-message">No se pudieron cargar los detalles</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = `<p class="error-message">Error al cargar los detalles</p>`;
    }
}

function buildDetailsHTML(zona) {
    const empleados = zona.empleados || [];
    const geosegmentos = zona.geosegmentos || [];
    const esCerrado = isCicloCerrado();

    return `
        <div class="zone-details-body">
            <!-- Información Básica -->
            <div class="zone-basic-info">
                <div class="info-item">
                    <span class="info-label">Zona:</span>
                    <span class="info-value">${zona.zona}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <span class="status-badge status-${zona.estado?.estado?.toLowerCase() || 'inactivo'}">
                            <span class="status-dot"></span>
                            ${zona.estado?.estado || 'Inactivo'}
                        </span>
                    </span>
                </div>
            </div>
            
            <div class="zone-details-grid">
                <!-- Empleados Column -->
                <div class="zone-detail-column">
                    <div class="zone-column-header">
                        <div class="zone-column-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Empleados</span>
                            <span class="zone-count-badge">${empleados.length}</span>
                        </div>
                        ${!esCerrado ? `
                            <button class="zone-add-btn" onclick="openAddEmpleadoModal()" title="Agregar empleado">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                    <div class="zone-column-content">
                        ${empleados.length > 0 ? empleados.map(emp => `
                            <div class="zone-employee-item">
                                <div class="zone-employee-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <span class="zone-employee-name">${emp.nombre}</span>
                                ${!esCerrado ? `
                                    <button class="zone-geo-remove" onclick="confirmRemoveEmpleado(${emp.id}, '${emp.nombre}')" title="Quitar">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                ` : ''}
                            </div>
                        `).join('') : '<div class="zone-empty-state">No hay empleados asignados</div>'}
                    </div>
                </div>

                <!-- Geosegmentos Column -->
                <div class="zone-detail-column">
                    <div class="zone-column-header">
                        <div class="zone-column-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>Geosegmentos</span>
                            <span class="zone-count-badge">${geosegmentos.length}</span>
                        </div>
                        ${!esCerrado ? `
                            <button class="zone-add-btn" onclick="openAddGeosegmentoModal()" title="Agregar geosegmento">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                    <div class="zone-column-content zone-geo-grid">
                        ${geosegmentos.length > 0 ? geosegmentos.map(geo => `
                            <div class="zone-geo-item">
                                <div class="zone-geo-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <span class="zone-geo-name">${geo.geosegmento}</span>
                                ${!esCerrado ? `
                                    <button class="zone-geo-remove" onclick="confirmRemoveGeosegmento(${geo.id}, \`${geo.geosegmento}\`)" title="Quitar">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                ` : ''}
                            </div>
                        `).join('') : '<div class="zone-empty-state">No hay geosegmentos asignados</div>'}
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Ubigeos:</span>
                    <span class="info-value">
                        <span class="count-badge-inline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                            <span>${zona.ubigeos_count || 0}</span>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    `;
}

// ==========================================
// 6. ADD/REMOVE GEOSEGMENTOS
// ==========================================



async function addGeosegmentoToZone(geoId) {
    const cycleFilter = document.getElementById('cycleFilter');
    const cicloId = cycleFilter ? cycleFilter.value : null;

    if (!cicloId) {
        showToast('Debes seleccionar un ciclo primero', 'warning');
        return;
    }

    try {
        const response = await fetch(`/zonas/${currentZoneId}/geosegmentos`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idGeosegmento: geoId,
                idCiclo: cicloId
            })
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            viewZoneDetails(currentZoneId);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al agregar el geosegmento', 'error');
    }
}

async function removeGeosegmentoFromZone(geoId) {
    try {
        const response = await fetch(`/zonas/geosegmentos/${geoId}/deactivate`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            // Actualizar solo la modal de detalles
            await viewZoneDetails(currentZoneId);
            // Actualizar la tabla en segundo plano
            await reloadZonesTable();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al quitar el geosegmento', 'error');
    }
}

// ==========================================
// MODAL AGREGAR EMPLEADOS
// ==========================================

let selectedEmpleados = [];

window.openAddEmpleadoModal = function () {
    const modal = document.getElementById('addEmpleadoModal');
    const searchInput = document.getElementById('empSearchInput');

    selectedEmpleados = [];
    searchInput.value = '';
    loadEmpleados();

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeAddEmpleadoModal = function () {
    const modal = document.getElementById('addEmpleadoModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        selectedEmpleados = [];
    }, 300);
}

window.searchEmpleados = function () {
    loadEmpleados();
}

function loadEmpleados() {
    const searchTerm = document.getElementById('empSearchInput').value.toLowerCase().trim();
    const container = document.getElementById('empList');

    // Si no hay término de búsqueda, mostrar mensaje inicial
    if (!searchTerm) {
        container.innerHTML = `
            <div class="geo-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>Escribe para buscar empleados...</p>
            </div>
        `;
        return;
    }

    if (!window.empleadosData || window.empleadosData.length === 0) {
        container.innerHTML = `
            <div class="geo-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 8v4M12 16h.01"></path>
                </svg>
                <p>No hay empleados disponibles</p>
            </div>
        `;
        return;
    }

    const filtered = window.empleadosData.filter(emp =>
        emp.nombre.toLowerCase().includes(searchTerm)
    );

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="geo-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>No se encontraron empleados con "${searchTerm}"</p>
            </div>
        `;
        return;
    }

    container.innerHTML = filtered.map(emp => `
        <div class="emp-list-item ${selectedEmpleados.includes(emp.idEmpleado) ? 'selected' : ''}" 
             onclick="toggleEmpleado(${emp.idEmpleado})">
            <div class="emp-list-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <span class="emp-list-name">${emp.nombre}</span>
            <div class="emp-list-check">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
        </div>
    `).join('');
}

window.toggleEmpleado = function (empId) {
    const index = selectedEmpleados.indexOf(empId);

    if (index > -1) {
        selectedEmpleados.splice(index, 1);
    } else {
        selectedEmpleados.push(empId);
    }

    loadEmpleados();
}

window.confirmSaveEmpleados = function () {
    if (selectedEmpleados.length === 0) {
        showToast('Debes seleccionar al menos un empleado', 'warning');
        return;
    }

    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');
    const confirmBtn = document.getElementById('confirmButton');

    title.textContent = 'Confirmar Agregar';
    message.textContent = `¿Deseas agregar ${selectedEmpleados.length} empleado${selectedEmpleados.length !== 1 ? 's' : ''} a esta zona?`;
    buttonText.textContent = 'Agregar';

    confirmBtn.className = 'btn btn-primary';

    pendingAction = {
        type: 'addEmpleados',
        empIds: selectedEmpleados
    };

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.confirmRemoveEmpleado = function (empId, empName) {
    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');
    const confirmBtn = document.getElementById('confirmButton');

    title.textContent = 'Confirmar Quitar';
    message.textContent = `¿Estás seguro de que deseas quitar al empleado "${empName}" de esta zona?`;
    buttonText.textContent = 'Quitar';

    confirmBtn.className = 'btn btn-danger';

    pendingAction = {
        type: 'removeEmpleado',
        empId: empId
    };

    modal.classList.remove('closing');
    modal.classList.add('active');
}

async function addMultipleEmpleadosToZone(empIds) {
    const cycleFilter = document.getElementById('cycleFilter');
    const cicloId = cycleFilter ? cycleFilter.value : null;

    if (!cicloId) {
        showToast('Debes seleccionar un ciclo primero', 'warning');
        return;
    }

    closeAddEmpleadoModal();

    try {
        let successCount = 0;
        let errorCount = 0;

        for (const empId of empIds) {
            const response = await fetch(`/zonas/${currentZoneId}/empleados`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idEmpleado: empId,
                    idCiclo: cicloId
                })
            });

            const result = await response.json();

            if (result.success) {
                successCount++;
            } else {
                errorCount++;
            }
        }

        if (successCount > 0) {
            showToast(`${successCount} empleado${successCount !== 1 ? 's' : ''} agregado${successCount !== 1 ? 's' : ''} exitosamente`, 'success');
            // Actualizar solo la modal de detalles
            await viewZoneDetails(currentZoneId);
            // Actualizar la tabla en segundo plano
            await reloadZonesTable();
        }

        if (errorCount > 0) {
            showToast(`${errorCount} empleado${errorCount !== 1 ? 's' : ''} no pudo${errorCount !== 1 ? 'ieron' : ''} ser agregado${errorCount !== 1 ? 's' : ''}`, 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al agregar empleados', 'error');
    }
}

async function removeEmpleadoFromZone(empId) {
    try {
        const response = await fetch(`/zonas/empleados/${empId}/deactivate`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            // Actualizar solo la modal de detalles
            await viewZoneDetails(currentZoneId);
            // Actualizar la tabla en segundo plano
            await reloadZonesTable();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al quitar el empleado', 'error');
    }
}

// ==========================================
// 7. FILTERS
// ==========================================

window.filterByCycle = function () {
    const select = document.getElementById('cycleFilter');
    const selectedOption = select.options[select.selectedIndex];
    const cycleId = select.value;
    const esCerrado = selectedOption.getAttribute('data-cerrado') === 'true';

    // Obtener el ciclo actual de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentCycleId = urlParams.get('ciclo') || '';

    // Si el ciclo seleccionado es el mismo que el actual, no hacer nada
    if (cycleId === currentCycleId) {
        return;
    }

    cicloSeleccionado = cycleId;

    // Mostrar/ocultar advertencia
    const warning = document.getElementById('cycleClosedWarning');
    if (warning) {
        warning.style.display = esCerrado && cycleId ? 'flex' : 'none';
    }

    // Recargar con el filtro
    if (cycleId) {
        window.location.href = `/zonas?ciclo=${cycleId}`;
    } else {
        window.location.href = '/zonas';
    }
}

window.searchZones = function () {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#zonesTableBody tr');

        rows.forEach(row => {
            const zoneName = row.getAttribute('data-zone-name');
            if (zoneName) {
                const matches = zoneName.toLowerCase().includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            }
        });
    }, 300);
}

// ==========================================
// 8. UTILITY FUNCTIONS
// ==========================================

function isCicloCerrado() {
    const select = document.getElementById('cycleFilter');
    if (!select || !select.value) return false;

    const selectedOption = select.options[select.selectedIndex];
    return selectedOption.getAttribute('data-cerrado') === 'true';
}

/**
 * Recarga la tabla de zonas sin refrescar la página completa
 */
async function reloadZonesTable() {
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : '';

        const url = cicloId ? `/zonas?ciclo=${cicloId}` : '/zonas';

        const response = await fetch(url, {
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const html = await response.text();

        // Crear un elemento temporal para parsear el HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        // Extraer solo el tbody de la tabla
        const newTableBody = tempDiv.querySelector('#zonesTableBody');
        const currentTableBody = document.getElementById('zonesTableBody');

        if (newTableBody && currentTableBody) {
            currentTableBody.innerHTML = newTableBody.innerHTML;
        }

        // Actualizar la paginación si existe
        const newPagination = tempDiv.querySelector('.pagination-wrapper');
        const currentPagination = document.querySelector('.pagination-wrapper');

        if (newPagination && currentPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        }

    } catch (error) {
        console.error('Error al recargar la tabla:', error);
        showToast('Error al actualizar la tabla', 'error');
    }
}

// ==========================================
// 9. INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Zonas cargado correctamente');

    // Cerrar modales con ESC - Solo cierra la modal más arriba
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // Verificar qué modales están abiertas y cerrar la de mayor prioridad
            const confirmModal = document.getElementById('confirmModal');
            const createGeosegmentoModal = document.getElementById('createGeosegmentoModal');
            const addEmpleadoModal = document.getElementById('addEmpleadoModal');
            const addGeosegmentoModal = document.getElementById('addGeosegmentoModal');
            const detailsModal = document.getElementById('detailsModal');
            const zoneModal = document.getElementById('zoneModal');

            // Cerrar en orden de prioridad (z-index)
            const assignUbigeosModal = document.getElementById('assignUbigeosModal');
            
            if (confirmModal && confirmModal.classList.contains('active')) {
                closeConfirmModal();
            } else if (assignUbigeosModal && assignUbigeosModal.classList.contains('active')) {
                closeAssignUbigeosModal();
            } else if (createGeosegmentoModal && createGeosegmentoModal.classList.contains('active')) {
                closeCreateGeosegmentoModal();
            } else if (addEmpleadoModal && addEmpleadoModal.classList.contains('active')) {
                closeAddEmpleadoModal();
            } else if (addGeosegmentoModal && addGeosegmentoModal.classList.contains('active')) {
                closeAddGeosegmentoModal();
            } else if (detailsModal && detailsModal.classList.contains('active')) {
                closeDetailsModal();
            } else if (zoneModal && zoneModal.classList.contains('active')) {
                closeModal();
            }
        }
    });

    // Cerrar modales al hacer clic fuera
    const modals = ['zoneModal', 'detailsModal', 'confirmModal', 'addGeosegmentoModal', 'addEmpleadoModal', 'createGeosegmentoModal', 'assignUbigeosModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    if (modalId === 'zoneModal') closeModal();
                    else if (modalId === 'detailsModal') closeDetailsModal();
                    else if (modalId === 'confirmModal') closeConfirmModal();
                    else if (modalId === 'addGeosegmentoModal') closeAddGeosegmentoModal();
                    else if (modalId === 'addEmpleadoModal') closeAddEmpleadoModal();
                    else if (modalId === 'createGeosegmentoModal') closeCreateGeosegmentoModal();
                    else if (modalId === 'assignUbigeosModal') closeAssignUbigeosModal();
                }
            });
        }
    });

    // Establecer el ciclo seleccionado desde la URL o el valor por defecto del servidor
    const cycleFilter = document.getElementById('cycleFilter');
    if (cycleFilter) {
        const urlParams = new URLSearchParams(window.location.search);
        const cicloFromUrl = urlParams.get('ciclo');

        if (cicloFromUrl) {
            cycleFilter.value = cicloFromUrl;
            cicloSeleccionado = cicloFromUrl;
        } else if (cycleFilter.value) {
            // Si no hay ciclo en URL pero el select tiene un valor (seleccionado por el servidor)
            cicloSeleccionado = cycleFilter.value;
        }

        // Verificar si hay un ciclo cerrado seleccionado
        if (cycleFilter.value) {
            const selectedOption = cycleFilter.options[cycleFilter.selectedIndex];
            const esCerrado = selectedOption.getAttribute('data-cerrado') === 'true';

            const warning = document.getElementById('cycleClosedWarning');
            if (warning && esCerrado) {
                warning.style.display = 'flex';
            }
        }
    }
});


// ==========================================
// MODAL AGREGAR GEOSEGMENTOS
// ==========================================

let selectedGeosegmentos = [];

// ==========================================
// MODAL CREAR GEOSEGMENTO
// ==========================================

window.openCreateGeosegmentoModal = function () {
    const modal = document.getElementById('createGeosegmentoModal');
    const form = document.getElementById('createGeosegmentoForm');

    form.reset();

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeCreateGeosegmentoModal = function () {
    const modal = document.getElementById('createGeosegmentoModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

window.saveNewGeosegmento = async function (event) {
    event.preventDefault();

    const geosegmento = document.getElementById('newGeosegmento').value.trim();
    const lugar = document.getElementById('newLugar').value.trim();

    if (!geosegmento) {
        showToast('El nombre del geosegmento es requerido', 'warning');
        return;
    }

    try {
        const data = {
            geosegmento: geosegmento
        };

        // Solo agregar lugar si tiene valor
        if (lugar) {
            data.lugar = lugar;
        }

        const response = await fetch('/geosegmentos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            closeCreateGeosegmentoModal();

            // Actualizar la lista de geosegmentos
            if (result.data && result.data.idGeosegmento) {
                // Agregar el nuevo geosegmento a la lista global
                if (!window.geosegmentosData) {
                    window.geosegmentosData = [];
                }
                window.geosegmentosData.push({
                    idGeosegmento: result.data.idGeosegmento,
                    geosegmento: result.data.geosegmento,
                    lugar: result.data.lugar || ''
                });

                // Recargar la lista de geosegmentos en la modal
                loadGeosegmentos();
                
                // Mostrar toast con opción de agregar ubigeos
                showToastWithAction(
                    `Geosegmento "${result.data.geosegmento}" creado exitosamente`,
                    'success',
                    '¿Deseas agregar ubigeos ahora?',
                    'Agregar Ubigeos',
                    () => openAssignUbigeosModal(result.data.idGeosegmento, result.data.geosegmento)
                );
            } else {
                showToast(result.message || 'Geosegmento creado exitosamente', 'success');
            }
        } else {
            showToast(result.message || 'Error al crear el geosegmento', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al crear el geosegmento', 'error');
    }
}

window.openAddGeosegmentoModal = function () {
    const modal = document.getElementById('addGeosegmentoModal');
    const searchInput = document.getElementById('geoSearchInput');

    selectedGeosegmentos = [];
    searchInput.value = '';
    loadGeosegmentos();

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeAddGeosegmentoModal = function () {
    const modal = document.getElementById('addGeosegmentoModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        selectedGeosegmentos = [];
    }, 300);
}

window.searchGeosegmentos = function () {
    loadGeosegmentos();
}

function loadGeosegmentos() {
    const searchTerm = document.getElementById('geoSearchInput').value.toLowerCase();
    const container = document.getElementById('geoGrid');

    if (!window.geosegmentosData || window.geosegmentosData.length === 0) {
        container.innerHTML = `
            <div class="geo-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 8v4M12 16h.01"></path>
                </svg>
                <p>No hay geosegmentos disponibles</p>
            </div>
        `;
        return;
    }

    const filtered = searchTerm
        ? window.geosegmentosData.filter(geo => geo.geosegmento.toLowerCase().includes(searchTerm))
        : window.geosegmentosData;

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="geo-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>No se encontraron geosegmentos</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="geo-cards-grid">
            ${filtered.map(geo => `
                <div class="geo-card ${selectedGeosegmentos.includes(geo.idGeosegmento) ? 'selected' : ''}" 
                     onclick="toggleGeosegmento(${geo.idGeosegmento})">
                    <div class="geo-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <span class="geo-card-text">${geo.geosegmento}</span>
                    <div class="geo-card-check">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

window.toggleGeosegmento = function (geoId) {
    const index = selectedGeosegmentos.indexOf(geoId);

    if (index > -1) {
        selectedGeosegmentos.splice(index, 1);
    } else {
        selectedGeosegmentos.push(geoId);
    }

    loadGeosegmentos();
}

window.confirmSaveGeosegmentos = function () {
    if (selectedGeosegmentos.length === 0) {
        showToast('Debes seleccionar al menos un geosegmento', 'warning');
        return;
    }

    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');
    const confirmBtn = document.getElementById('confirmButton');

    title.textContent = 'Confirmar Agregar';
    message.textContent = `¿Deseas agregar ${selectedGeosegmentos.length} geosegmento${selectedGeosegmentos.length !== 1 ? 's' : ''} a esta zona?`;
    buttonText.textContent = 'Agregar';

    confirmBtn.className = 'btn btn-primary';

    pendingAction = {
        type: 'addGeosegmentos',
        geoIds: selectedGeosegmentos
    };

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.confirmRemoveGeosegmento = function (geoId, geoName) {
    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');
    const confirmBtn = document.getElementById('confirmButton');

    title.textContent = 'Confirmar Quitar';
    message.textContent = `¿Estás seguro de que deseas quitar el geosegmento "${geoName}" de esta zona?`;
    buttonText.textContent = 'Quitar';

    confirmBtn.className = 'btn btn-danger';

    pendingAction = {
        type: 'removeGeosegmento',
        geoId: geoId
    };

    modal.classList.remove('closing');
    modal.classList.add('active');
}


async function addMultipleGeosegmentosToZone(geoIds) {
    const cycleFilter = document.getElementById('cycleFilter');
    const cicloId = cycleFilter ? cycleFilter.value : null;

    if (!cicloId) {
        showToast('Debes seleccionar un ciclo primero', 'warning');
        return;
    }

    closeAddGeosegmentoModal();

    try {
        let successCount = 0;
        let errorCount = 0;

        for (const geoId of geoIds) {
            const response = await fetch(`/zonas/${currentZoneId}/geosegmentos`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idGeosegmento: geoId,
                    idCiclo: cicloId
                })
            });

            const result = await response.json();

            if (result.success) {
                successCount++;
            } else {
                errorCount++;
            }
        }

        if (successCount > 0) {
            showToast(`${successCount} geosegmento${successCount !== 1 ? 's' : ''} agregado${successCount !== 1 ? 's' : ''} exitosamente`, 'success');
            // Actualizar solo la modal de detalles
            await viewZoneDetails(currentZoneId);
            // Actualizar la tabla en segundo plano
            await reloadZonesTable();
        }

        if (errorCount > 0) {
            showToast(`${errorCount} geosegmento${errorCount !== 1 ? 's' : ''} no pudo${errorCount !== 1 ? 'ieron' : ''} ser agregado${errorCount !== 1 ? 's' : ''}`, 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al agregar los geosegmentos', 'error');
    }
}


// ==========================================
// MODAL ASIGNAR UBIGEOS A GEOSEGMENTO
// ==========================================

let currentGeosegmentoId = null;
let currentGeosegmentoName = null;
let selectedUbigeos = [];

window.openAssignUbigeosModal = function (geoId, geoName) {
    currentGeosegmentoId = geoId;
    currentGeosegmentoName = geoName;
    selectedUbigeos = [];
    
    const modal = document.getElementById('assignUbigeosModal');
    const title = document.getElementById('assignUbigeosTitle');
    const searchInput = document.getElementById('ubigeoSearchInput');
    
    title.textContent = `Agregar Ubigeos a "${geoName}"`;
    searchInput.value = '';
    
    loadAvailableUbigeos();
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeAssignUbigeosModal = function () {
    const modal = document.getElementById('assignUbigeosModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        currentGeosegmentoId = null;
        currentGeosegmentoName = null;
        selectedUbigeos = [];
    }, 300);
}

window.searchUbigeos = function () {
    loadAvailableUbigeos();
}

function loadAvailableUbigeos() {
    const searchTerm = document.getElementById('ubigeoSearchInput').value.toLowerCase().trim();
    const container = document.getElementById('ubigeosList');
    
    if (!searchTerm) {
        container.innerHTML = `
            <div class="ubigeo-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>Escribe para buscar ubigeos por departamento, provincia o distrito...</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div class="ubigeo-loading">
            <div class="spinner"></div>
            <p>Buscando ubigeos...</p>
        </div>
    `;
    
    // Buscar ubigeos disponibles (sin geosegmento asignado o del geosegmento actual)
    fetch(`/api/ubigeos/search?q=${encodeURIComponent(searchTerm)}&geosegmento=${currentGeosegmentoId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data.length > 0) {
                renderUbigeosList(result.data);
            } else {
                container.innerHTML = `
                    <div class="ubigeo-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 8v4M12 16h.01"></path>
                        </svg>
                        <p>No se encontraron ubigeos con "${searchTerm}"</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="ubigeo-empty error">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <p>Error al buscar ubigeos</p>
                </div>
            `;
        });
}

function renderUbigeosList(ubigeos) {
    const container = document.getElementById('ubigeosList');
    
    container.innerHTML = ubigeos.map(ubigeo => `
        <div class="ubigeo-item ${selectedUbigeos.includes(ubigeo.idUbigeo) ? 'selected' : ''}" 
             onclick="toggleUbigeo(${ubigeo.idUbigeo})">
            <div class="ubigeo-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <div class="ubigeo-item-content">
                <div class="ubigeo-item-title">${ubigeo.distrito}</div>
                <div class="ubigeo-item-subtitle">${ubigeo.provincia}, ${ubigeo.departamento}</div>
            </div>
            <div class="ubigeo-item-check">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
        </div>
    `).join('');
}

window.toggleUbigeo = function (ubigeoId) {
    const index = selectedUbigeos.indexOf(ubigeoId);
    
    if (index > -1) {
        selectedUbigeos.splice(index, 1);
    } else {
        selectedUbigeos.push(ubigeoId);
    }
    
    // Actualizar visualmente
    const item = document.querySelector(`.ubigeo-item[onclick="toggleUbigeo(${ubigeoId})"]`);
    if (item) {
        item.classList.toggle('selected');
    }
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const countElement = document.getElementById('selectedUbigeosCount');
    if (countElement) {
        countElement.textContent = selectedUbigeos.length;
    }
}

window.confirmAssignUbigeos = async function () {
    if (selectedUbigeos.length === 0) {
        showToast('Debes seleccionar al menos un ubigeo', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`/geosegmentos/${currentGeosegmentoId}/ubigeos`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ubigeos: selectedUbigeos
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(`${selectedUbigeos.length} ubigeo${selectedUbigeos.length !== 1 ? 's' : ''} asignado${selectedUbigeos.length !== 1 ? 's' : ''} exitosamente`, 'success');
            closeAssignUbigeosModal();
        } else {
            showToast(result.message || 'Error al asignar ubigeos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al asignar ubigeos', 'error');
    }
}
