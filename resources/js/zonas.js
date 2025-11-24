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
    } else if (pendingAction.type === 'addRepresentantes') {
        await addMultipleRepresentantesToZone(pendingAction.empIds, pendingAction.idZonaEmp, pendingAction.idFranqLinea);
    } else if (pendingAction.type === 'removeEmpleado') {
        await removeEmpleadoFromZone(pendingAction.empId);
    } else if (pendingAction.type === 'unificarLinea') {
        await executeUnificarLinea();
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
                            <span class="zone-count-badge">${empleados.filter(e => e.tipo === 'supervisor').length} Sup, ${empleados.filter(e => e.tipo === 'representante').length} Rep</span>
                        </div>
                        ${!esCerrado ? `
                            <div class="zone-header-actions">
                                <button class="zone-unify-btn" onclick="openUnificarLineaModal()" title="Unificar líneas">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                                    </svg>
                                </button>
                                <button class="zone-add-btn" onclick="openAddEmpleadoModal()" title="Agregar empleado">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    <div class="zone-column-content">
                        ${empleados.length > 0 ? empleados.map(emp => `
                            <div class="zone-employee-item ${emp.tipo === 'representante' ? 'representante' : 'supervisor'} ${emp.esVacante ? 'vacante' : ''} ${emp.esLicencia ? 'licencia' : ''}">
                                <div class="zone-employee-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        ${emp.tipo === 'supervisor' ? `
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        ` : `
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        `}
                                    </svg>
                                </div>
                                <div class="zone-employee-info">
                                    <span class="zone-employee-name">${emp.nombre}</span>
                                    ${!emp.esVacante ? `
                                        <div class="zone-employee-badges">
                                            <span class="zone-employee-badge">${emp.cargo || emp.tipo}</span>
                                            ${emp.esLicencia ? `<span class="zone-employee-badge badge-licencia">LICENCIA</span>` : ''}
                                        </div>
                                    ` : ''}
                                    ${emp.tipo === 'representante' && emp.linea && !emp.esVacante ? `
                                        <span class="zone-employee-supervisor">${emp.linea}</span>
                                    ` : ''}
                                </div>
                                ${!esCerrado ? `
                                    <div class="zone-employee-actions">
                                        ${emp.tipo === 'supervisor' ? `
                                            <button class="zone-action-btn zone-action-change" onclick="openCambiarEmpleadoModal(${emp.id}, ${emp.idEmpleado}, '${emp.nombre}', 'supervisor')" title="Cambiar supervisor">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                                                </svg>
                                            </button>
                                            ${!emp.esVacante ? `
                                                <button class="zone-action-btn zone-action-remove" onclick="confirmRemoveEmpleado(${emp.id}, '${emp.nombre}')" title="Quitar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                </button>
                                            ` : ''}
                                        ` : `
                                            ${emp.esLicencia ? `
                                                <button class="zone-action-btn zone-action-change" onclick="openCambiarEmpleadoModal(${emp.id}, ${emp.idEmpleado}, '${emp.nombre}', 'representante', '${emp.linea}')" title="Cambiar representante">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                                                    </svg>
                                                </button>
                                                <button class="zone-action-btn zone-action-activate" onclick="confirmReactivarEmpleado(${emp.idEmpleado}, '${emp.nombre}')" title="Reactivar empleado">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="23 4 23 10 17 10"></polyline>
                                                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                                    </svg>
                                                    Activar
                                                </button>
                                            ` : `
                                                <button class="zone-action-btn zone-action-change" onclick="openCambiarEmpleadoModal(${emp.id}, ${emp.idEmpleado}, '${emp.nombre}', 'representante', '${emp.linea}')" title="Cambiar representante">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                                                    </svg>
                                                </button>
                                                ${!emp.esVacante ? `
                                                    <button class="zone-action-btn zone-action-remove" onclick="openAusenciaModal(${emp.id}, ${emp.idEmpleado}, '${emp.nombre}')" title="Quitar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                        </svg>
                                                    </button>
                                                ` : ''}
                                            `}
                                        `}
                                    </div>
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
                            <div class="zone-header-actions">
                                <button class="zone-clone-btn" onclick="openCloneGeosegmentosModal()" title="Clonar desde otras zonas">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </button>
                                <button class="zone-add-btn" onclick="openAddGeosegmentoModal()" title="Agregar geosegmento">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    ${!esCerrado && geosegmentos.length > 0 ? `
                        <div class="zone-bulk-actions" id="zoneBulkActions" style="display: none;">
                            <div class="zone-bulk-info">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                </svg>
                                <span id="zoneBulkCount">0</span> seleccionado(s)
                            </div>
                            <button class="zone-bulk-remove-btn" onclick="bulkRemoveGeosegmentos()" title="Eliminar seleccionados">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Eliminar
                            </button>
                        </div>
                    ` : ''}
                    <div class="zone-column-content zone-geo-grid">
                        ${geosegmentos.length > 0 ? geosegmentos.map(geo => `
                            <div class="zone-geo-item">
                                ${!esCerrado ? `
                                    <div class="zone-geo-checkbox">
                                        <input type="checkbox" 
                                               class="geo-bulk-checkbox" 
                                               id="geo-bulk-${geo.id}" 
                                               value="${geo.id}"
                                               onchange="updateZoneBulkActions()">
                                        <label for="geo-bulk-${geo.id}"></label>
                                    </div>
                                ` : ''}
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
let currentZonaSupervisores = [];

window.openAddEmpleadoModal = function () {
    const modal = document.getElementById('addEmpleadoModal');
    const searchInput = document.getElementById('empSearchInput');
    const tipoEmpleado = document.getElementById('tipoEmpleado');

    selectedEmpleados = [];
    searchInput.value = '';
    tipoEmpleado.value = 'supervisor';
    
    // Cargar supervisores de la zona actual
    loadZonaSupervisores();
    
    handleTipoEmpleadoChange();
    loadEmpleados();

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.handleTipoEmpleadoChange = function() {
    const tipoEmpleado = document.getElementById('tipoEmpleado').value;
    const supervisorGroup = document.getElementById('supervisorGroup');
    const franqLineaGroup = document.getElementById('franqLineaGroup');
    
    if (tipoEmpleado === 'representante') {
        supervisorGroup.style.display = 'block';
        franqLineaGroup.style.display = 'block';
        loadFranqLineas();
    } else {
        supervisorGroup.style.display = 'none';
        franqLineaGroup.style.display = 'none';
    }
}

async function loadFranqLineas() {
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        if (!cicloId) {
            showToast('Debes seleccionar un ciclo primero', 'warning');
            return;
        }
        
        const response = await fetch(`/api/franqlineas?ciclo=${cicloId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const franqLineaSelect = document.getElementById('franqLineaSelect');
            franqLineaSelect.innerHTML = '<option value="">Seleccione una franquicia/línea...</option>' +
                result.data.map(fl => 
                    `<option value="${fl.idFranqLinea}">${fl.franqLinea}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error cargando franqlineas:', error);
    }
}

async function loadZonaSupervisores() {
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        const url = cicloId
            ? `/zonas/${currentZoneId}/empleados?ciclo=${cicloId}`
            : `/zonas/${currentZoneId}/empleados`;
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success && result.data.supervisores) {
            currentZonaSupervisores = result.data.supervisores;
            
            // Llenar el select de supervisores
            const supervisorSelect = document.getElementById('supervisorSelect');
            supervisorSelect.innerHTML = '<option value="">Seleccione un supervisor...</option>' +
                currentZonaSupervisores.map(sup => 
                    `<option value="${sup.idZonaEmp}">${sup.nombre}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error cargando supervisores:', error);
    }
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

    const tipoEmpleado = document.getElementById('tipoEmpleado').value;
    
    // Si es representante, validar que haya seleccionado un supervisor y franqlinea
    if (tipoEmpleado === 'representante') {
        const supervisorSelect = document.getElementById('supervisorSelect');
        const franqLineaSelect = document.getElementById('franqLineaSelect');
        
        if (!supervisorSelect.value) {
            showToast('Debes seleccionar un supervisor para los representantes', 'warning');
            return;
        }
        
        if (!franqLineaSelect.value) {
            showToast('Debes seleccionar una franquicia/línea para los representantes', 'warning');
            return;
        }
    }

    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');
    const confirmBtn = document.getElementById('confirmButton');

    const tipoTexto = tipoEmpleado === 'supervisor' ? 'supervisor' : 'representante médico';
    title.textContent = 'Confirmar Agregar';
    message.textContent = `¿Deseas agregar ${selectedEmpleados.length} ${tipoTexto}${selectedEmpleados.length !== 1 ? 's' : ''} a esta zona?`;
    buttonText.textContent = 'Agregar';

    confirmBtn.className = 'btn btn-primary';

    pendingAction = {
        type: tipoEmpleado === 'supervisor' ? 'addEmpleados' : 'addRepresentantes',
        empIds: selectedEmpleados,
        idZonaEmp: tipoEmpleado === 'representante' ? document.getElementById('supervisorSelect').value : null,
        idFranqLinea: tipoEmpleado === 'representante' ? document.getElementById('franqLineaSelect').value : null
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

async function addMultipleRepresentantesToZone(empIds, idZonaEmp, idFranqLinea) {
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
            const response = await fetch(`/zonas/${currentZoneId}/representantes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idEmpleado: empId,
                    idZonaEmp: idZonaEmp,
                    idCiclo: cicloId,
                    idFranqLinea: idFranqLinea
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
            showToast(`${successCount} representante${successCount !== 1 ? 's' : ''} agregado${successCount !== 1 ? 's' : ''} exitosamente`, 'success');
            // Actualizar solo la modal de detalles
            await viewZoneDetails(currentZoneId);
            // Actualizar la tabla en segundo plano
            await reloadZonesTable();
        }

        if (errorCount > 0) {
            showToast(`${errorCount} representante${errorCount !== 1 ? 's' : ''} no pudo${errorCount !== 1 ? 'ieron' : ''} ser agregado${errorCount !== 1 ? 's' : ''}`, 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al agregar representantes', 'error');
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
        applyZonesFilters();
    }, 500);
}

function applyZonesFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    
    // Construir URL con parámetros
    const url = new URL(window.location.href);
    url.searchParams.delete('page'); // Reset pagination
    
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    
    // Recargar página con nuevos parámetros
    window.location.href = url.toString();
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
            const cloneGeosegmentosModal = document.getElementById('cloneGeosegmentosModal');
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
            } else if (cloneGeosegmentosModal && cloneGeosegmentosModal.classList.contains('active')) {
                closeCloneGeosegmentosModal();
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
    const modals = ['zoneModal', 'detailsModal', 'confirmModal', 'cloneGeosegmentosModal', 'addGeosegmentoModal', 'addEmpleadoModal', 'createGeosegmentoModal', 'assignUbigeosModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    if (modalId === 'zoneModal') closeModal();
                    else if (modalId === 'detailsModal') closeDetailsModal();
                    else if (modalId === 'confirmModal') closeConfirmModal();
                    else if (modalId === 'cloneGeosegmentosModal') closeCloneGeosegmentosModal();
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


// ==========================================
// CLONE GEOSEGMENTOS FROM OTHER ZONES
// ==========================================

let zonasForClone = [];

window.openCloneGeosegmentosModal = async function() {
    const modal = document.getElementById('cloneGeosegmentosModal');
    
    modal.classList.remove('closing');
    modal.classList.add('active');
    
    // Cargar zonas
    await loadZonasForClone();
}

window.closeCloneGeosegmentosModal = function() {
    const modal = document.getElementById('cloneGeosegmentosModal');
    modal.classList.add('closing');
    
    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        document.getElementById('cloneGeosegmentosForm').reset();
        document.getElementById('cloneGeosegmentosPreview').style.display = 'none';
    }, 300);
}

async function loadZonasForClone() {
    try {
        const response = await fetch('/api/zonas');
        const result = await response.json();
        
        if (result.success && result.data) {
            // Filtrar la zona actual
            zonasForClone = result.data.filter(z => z.idZona !== currentZoneId);
            renderZonasForClone(zonasForClone);
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('zonasOrigenCloneList').innerHTML = `
            <div class="error-message">Error al cargar las zonas</div>
        `;
    }
}

function renderZonasForClone(zonas) {
    const container = document.getElementById('cloneZonasList');
    
    if (zonas.length === 0) {
        container.innerHTML = '<div class="empty-message">No hay otras zonas disponibles</div>';
        return;
    }
    
    let html = '';
    zonas.forEach(zona => {
        html += `
            <div class="zone-checkbox-item" data-zona-name="${zona.zona.toLowerCase()}">
                <input type="checkbox" 
                       class="zone-origen-clone-checkbox" 
                       id="zona-clone-${zona.idZona}" 
                       value="${zona.idZona}"
                       onchange="updateCloneGeosegmentosPreview()">
                <label for="zona-clone-${zona.idZona}">
                    <div class="zone-checkbox-content">
                        <span class="zone-checkbox-name">${zona.zona}</span>
                        <span class="zone-checkbox-count">${zona.geosegmentos_count || 0} geosegmentos</span>
                    </div>
                </label>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

window.searchZonasForClone = function() {
    const searchTerm = document.getElementById('cloneZonaSearchInput').value.toLowerCase().trim();
    const zoneItems = document.querySelectorAll('.zone-checkbox-item');
    
    zoneItems.forEach(item => {
        const zonaName = item.getAttribute('data-zona-name');
        if (zonaName.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

window.updateCloneGeosegmentosPreview = async function() {
    const selectedOrigins = Array.from(document.querySelectorAll('.zone-origen-clone-checkbox:checked'))
        .map(cb => parseInt(cb.value));
    
    const previewEmpty = document.getElementById('cloneGeosegmentosPreview');
    const content = document.getElementById('cloneGeosegmentosPreviewContent');
    
    if (selectedOrigins.length === 0) {
        previewEmpty.style.display = 'flex';
        content.style.display = 'none';
        return;
    }
    
    const originsData = zonasForClone.filter(z => selectedOrigins.includes(z.idZona));
    
    // Mostrar loading
    content.innerHTML = `
        <div class="preview-loading">
            <div class="spinner-small"></div>
            <span>Calculando geosegmentos únicos...</span>
        </div>
    `;
    content.style.display = 'block';
    previewEmpty.style.display = 'none';
    
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        const response = await fetch(`/api/zonas/count-unique-geosegmentos?zonas=${selectedOrigins.join(',')}&ciclo=${cicloId}`);
        const result = await response.json();
        
        const totalGeosegmentos = result.success ? result.count : 0;
        
        content.innerHTML = `
            <div class="preview-item">
                <span class="preview-label">Zonas seleccionadas:</span>
                <span class="preview-value">${selectedOrigins.length}</span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Zonas origen:</span>
                <span class="preview-value">${originsData.map(z => z.zona).join(', ')}</span>
            </div>
            <div class="preview-item highlight">
                <span class="preview-label">Total de geosegmentos:</span>
                <span class="preview-value">${totalGeosegmentos} (únicos)</span>
            </div>
            <div class="preview-note">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <div>
                    <strong>⚠️ ADVERTENCIA:</strong> Los geosegmentos actuales de esta zona serán desactivados y reemplazados por los de las zonas seleccionadas.
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="preview-error">
                <span>Error al calcular geosegmentos</span>
            </div>
        `;
    }
}

window.confirmCloneGeosegmentos = async function(event) {
    event.preventDefault();
    
    const selectedOrigins = Array.from(document.querySelectorAll('.zone-origen-clone-checkbox:checked'))
        .map(cb => parseInt(cb.value));
    
    if (selectedOrigins.length === 0) {
        showToast('Debes seleccionar al menos una zona origen', 'warning');
        return;
    }
    
    // Mostrar modal de confirmación personalizado
    showConfirmModal(
        'Confirmar Reemplazo',
        '⚠️ ADVERTENCIA: Esta acción eliminará TODOS los geosegmentos actuales de esta zona y los reemplazará con los de las zonas seleccionadas.',
        '¿Estás seguro de continuar?',
        async () => {
            await executeCloneGeosegmentos(selectedOrigins);
        }
    );
}

async function executeCloneGeosegmentos(selectedOrigins) {
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        if (!cicloId) {
            showToast('Debes seleccionar un ciclo primero', 'warning');
            return;
        }
        
        const response = await fetch(`/zonas/${currentZoneId}/geosegmentos/copy-from-zones`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                zonasOrigen: selectedOrigins,
                idCiclo: parseInt(cicloId)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            closeCloneGeosegmentosModal();
            
            // Recargar detalles de la zona
            setTimeout(() => {
                viewZoneDetails(currentZoneId);
            }, 1000);
        } else {
            showToast(result.message || 'Error al clonar geosegmentos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al clonar geosegmentos', 'error');
    }
}


// ==========================================
// BULK REMOVE GEOSEGMENTOS
// ==========================================

let selectedGeosegmentosForRemoval = [];

window.updateZoneBulkActions = function() {
    const checkboxes = document.querySelectorAll('.geo-bulk-checkbox:checked');
    selectedGeosegmentosForRemoval = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    const bulkBar = document.getElementById('zoneBulkActions');
    const countElement = document.getElementById('zoneBulkCount');
    
    if (bulkBar && countElement) {
        if (selectedGeosegmentosForRemoval.length > 0) {
            bulkBar.style.display = 'flex';
            countElement.textContent = selectedGeosegmentosForRemoval.length;
        } else {
            bulkBar.style.display = 'none';
        }
    }
}

window.bulkRemoveGeosegmentos = function() {
    if (selectedGeosegmentosForRemoval.length === 0) {
        showToast('No hay geosegmentos seleccionados', 'warning');
        return;
    }
    
    showConfirmModal(
        'Confirmar Eliminación',
        `Vas a quitar ${selectedGeosegmentosForRemoval.length} geosegmento(s) de esta zona.`,
        '¿Estás seguro de continuar?',
        async () => {
            await executeBulkRemoveGeosegmentos();
        }
    );
}

async function executeBulkRemoveGeosegmentos() {
    try {
        const response = await fetch(`/zonas/${currentZoneId}/geosegmentos/bulk-remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                geosegmentos: selectedGeosegmentosForRemoval
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            selectedGeosegmentosForRemoval = [];
            
            // Recargar detalles de la zona
            setTimeout(() => {
                viewZoneDetails(currentZoneId);
            }, 1000);
        } else {
            showToast(result.message || 'Error al quitar geosegmentos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al quitar geosegmentos', 'error');
    }
}


// ==========================================
// MODAL AUSENCIA
// ==========================================

let currentAusenciaData = null;

window.openAusenciaModal = async function(idFuerza, idEmpleado, nombreEmpleado) {
    const modal = document.getElementById('ausenciaModal');
    
    // Cargar datos del empleado
    try {
        const response = await fetch(`/api/empleados/${idEmpleado}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            currentAusenciaData = {
                idFuerza: idFuerza,
                idEmpleado: idEmpleado,
                nombreEmpleado: nombreEmpleado,
                fechaIngreso: result.data.fechaIngreso,
                fechaCese: result.data.fechaCese
            };
        } else {
            currentAusenciaData = {
                idFuerza: idFuerza,
                idEmpleado: idEmpleado,
                nombreEmpleado: nombreEmpleado,
                fechaIngreso: null,
                fechaCese: null
            };
        }
    } catch (error) {
        console.error('Error cargando datos del empleado:', error);
        currentAusenciaData = {
            idFuerza: idFuerza,
            idEmpleado: idEmpleado,
            nombreEmpleado: nombreEmpleado,
            fechaIngreso: null,
            fechaCese: null
        };
    }
    
    document.getElementById('ausenciaIdFuerza').value = idFuerza;
    document.getElementById('ausenciaIdEmpleado').value = idEmpleado;
    document.getElementById('ausenciaNombreEmpleado').value = nombreEmpleado;
    document.getElementById('ausenciaIdTipoAusencia').value = '';
    document.getElementById('ausenciaFechaInicio').value = '';
    document.getElementById('ausenciaFechaFin').value = '';
    document.getElementById('ausenciaObservacion').value = '';
    
    // Habilitar campos de fecha por defecto
    document.getElementById('ausenciaFechaInicio').disabled = false;
    document.getElementById('ausenciaFechaFin').disabled = false;
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.handleTipoAusenciaChange = function() {
    const tipoSelect = document.getElementById('ausenciaIdTipoAusencia');
    const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
    const tipoAusencia = selectedOption.getAttribute('data-tipo');
    
    const fechaInicioInput = document.getElementById('ausenciaFechaInicio');
    const fechaFinInput = document.getElementById('ausenciaFechaFin');
    
    if (tipoAusencia === 'renuncia') {
        // Si es renuncia, rellenar automáticamente con fechas del empleado
        if (currentAusenciaData.fechaIngreso) {
            fechaInicioInput.value = currentAusenciaData.fechaIngreso;
            fechaInicioInput.disabled = true;
        }
        
        if (currentAusenciaData.fechaCese) {
            fechaFinInput.value = currentAusenciaData.fechaCese;
            fechaFinInput.disabled = true;
        }
        
        if (!currentAusenciaData.fechaIngreso || !currentAusenciaData.fechaCese) {
            showToast('No se encontraron las fechas de ingreso/cese del empleado', 'warning');
        }
    } else {
        // Para otros tipos (licencia, etc.), permitir ingreso manual
        fechaInicioInput.value = '';
        fechaFinInput.value = '';
        fechaInicioInput.disabled = false;
        fechaFinInput.disabled = false;
    }
}

window.closeAusenciaModal = function() {
    const modal = document.getElementById('ausenciaModal');
    modal.classList.add('closing');
    
    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        currentAusenciaData = null;
    }, 300);
}

window.saveAusencia = async function(event) {
    event.preventDefault();
    
    const idFuerza = document.getElementById('ausenciaIdFuerza').value;
    const idEmpleado = document.getElementById('ausenciaIdEmpleado').value;
    const idTipoAusencia = document.getElementById('ausenciaIdTipoAusencia').value;
    const fechaInicio = document.getElementById('ausenciaFechaInicio').value;
    const fechaFin = document.getElementById('ausenciaFechaFin').value;
    const observacion = document.getElementById('ausenciaObservacion').value;
    
    if (!idTipoAusencia) {
        showToast('Debes seleccionar un tipo de ausencia', 'warning');
        return;
    }
    
    if (!fechaInicio) {
        showToast('Debes ingresar la fecha de inicio', 'warning');
        return;
    }
    
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        if (!cicloId) {
            showToast('No hay un ciclo seleccionado', 'error');
            return;
        }
        
        const response = await fetch(`/zonas/representantes/${idFuerza}/remove-with-ausencia`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idEmpleado: idEmpleado,
                idTipoAusencia: idTipoAusencia,
                idCiclo: cicloId,
                fechaInicio: fechaInicio,
                fechaFin: fechaFin,
                observacion: observacion
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            closeAusenciaModal();
            
            // Recargar detalles de la zona
            setTimeout(() => {
                viewZoneDetails(currentZoneId);
            }, 500);
        } else {
            showToast(result.message || 'Error al registrar la ausencia', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al registrar la ausencia', 'error');
    }
}


// ==========================================
// FILTROS
// ==========================================

// Cargar líneas al iniciar
document.addEventListener('DOMContentLoaded', function() {
    loadLineas();
    initializeFilters();
});

function initializeFilters() {
    const cycleFilter = document.getElementById('cycleFilter');
    const empleadoInput = document.getElementById('empleadoSearchInput');
    const lineaFilter = document.getElementById('lineaFilter');
    
    // Verificar si hay un ciclo seleccionado
    const hasCycle = cycleFilter && cycleFilter.value;
    
    // Deshabilitar filtros que requieren ciclo si no hay uno seleccionado
    if (empleadoInput) {
        empleadoInput.disabled = !hasCycle;
        if (!hasCycle) {
            empleadoInput.placeholder = 'Selecciona un ciclo primero...';
        }
    }
    
    if (lineaFilter && !hasCycle) {
        lineaFilter.disabled = true;
    }
}

async function loadLineas() {
    try {
        const response = await fetch('/api/lineas');
        const result = await response.json();
        
        if (result.success && result.data) {
            const lineaFilter = document.getElementById('lineaFilter');
            const selectedValue = lineaFilter.getAttribute('data-selected');
            
            lineaFilter.innerHTML = '<option value="">Todas las líneas</option>' +
                result.data.map(linea => 
                    `<option value="${linea.idLinea}">${linea.linea}</option>`
                ).join('');
            
            // Restaurar valor seleccionado desde el parámetro URL
            if (selectedValue) {
                lineaFilter.value = selectedValue;
            }
        }
    } catch (error) {
        console.error('Error cargando líneas:', error);
    }
}

window.filterByCycle = function() {
    const cycleFilter = document.getElementById('cycleFilter');
    const selectedCycle = cycleFilter.value;
    const selectedOption = cycleFilter.options[cycleFilter.selectedIndex];
    const isCerrado = selectedOption ? selectedOption.getAttribute('data-cerrado') === 'true' : false;
    
    // Mostrar/ocultar advertencia de ciclo cerrado
    const warning = document.getElementById('cycleClosedWarning');
    if (warning) {
        warning.style.display = isCerrado ? 'flex' : 'none';
    }
    
    // Actualizar estado de filtros dependientes
    const empleadoInput = document.getElementById('empleadoSearchInput');
    const lineaFilter = document.getElementById('lineaFilter');
    
    if (empleadoInput) {
        empleadoInput.disabled = !selectedCycle;
        empleadoInput.placeholder = selectedCycle ? 'Buscar supervisor o representante...' : 'Selecciona un ciclo primero...';
    }
    
    if (lineaFilter) {
        lineaFilter.disabled = !selectedCycle;
    }
    
    // Recargar la página con el ciclo seleccionado
    const url = new URL(window.location.href);
    if (selectedCycle) {
        url.searchParams.set('ciclo', selectedCycle);
    } else {
        url.searchParams.delete('ciclo');
        // Si no hay ciclo, limpiar también los filtros que dependen del ciclo
        url.searchParams.delete('linea');
        url.searchParams.delete('empleado');
    }
    window.location.href = url.toString();
}

window.filterByLinea = function() {
    const lineaFilter = document.getElementById('lineaFilter');
    const cycleFilter = document.getElementById('cycleFilter');
    const selectedLinea = lineaFilter.value;
    const selectedCycle = cycleFilter.value;
    
    if (!selectedCycle) {
        showToast('Debes seleccionar un ciclo primero', 'warning');
        lineaFilter.value = '';
        return;
    }
    
    // Recargar la página con el filtro de línea
    const url = new URL(window.location.href);
    
    if (selectedLinea) {
        url.searchParams.set('linea', selectedLinea);
    } else {
        url.searchParams.delete('linea');
    }
    
    // Mantener el ciclo seleccionado
    if (selectedCycle) {
        url.searchParams.set('ciclo', selectedCycle);
    }
    
    window.location.href = url.toString();
}

window.searchZones = function() {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput.value.trim();
    
    // Usar debounce para evitar múltiples recargas
    clearTimeout(window.searchTimeout);
    
    window.searchTimeout = setTimeout(() => {
        const url = new URL(window.location.href);
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        window.location.href = url.toString();
    }, 500); // Esperar 500ms después de que el usuario deje de escribir
}

window.searchByEmpleado = function() {
    const empleadoInput = document.getElementById('empleadoSearchInput');
    const cycleFilter = document.getElementById('cycleFilter');
    const searchTerm = empleadoInput.value.trim();
    const selectedCycle = cycleFilter.value;
    
    if (!selectedCycle) {
        showToast('Debes seleccionar un ciclo primero', 'warning');
        empleadoInput.value = '';
        return;
    }
    
    // Usar debounce para evitar múltiples recargas
    clearTimeout(window.searchTimeout);
    
    window.searchTimeout = setTimeout(() => {
        const url = new URL(window.location.href);
        
        if (searchTerm) {
            url.searchParams.set('empleado', searchTerm);
        } else {
            url.searchParams.delete('empleado');
        }
        
        // Mantener el ciclo seleccionado
        if (selectedCycle) {
            url.searchParams.set('ciclo', selectedCycle);
        }
        
        window.location.href = url.toString();
    }, 500); // Esperar 500ms después de que el usuario deje de escribir
}


// ==========================================
// MODAL CAMBIAR EMPLEADO
// ==========================================

let currentCambioData = null;

window.openCambiarEmpleadoModal = function(id, idEmpleadoAntiguo, nombreAntiguo, tipo, linea = null) {
    const modal = document.getElementById('cambiarEmpleadoModal');
    const title = document.getElementById('cambiarEmpleadoTitle');
    
    currentCambioData = {
        id: id,
        idEmpleadoAntiguo: idEmpleadoAntiguo,
        nombreAntiguo: nombreAntiguo,
        tipo: tipo,
        linea: linea
    };
    
    title.textContent = tipo === 'supervisor' ? 'Cambiar Supervisor' : 'Cambiar Representante';
    document.getElementById('cambiarIdAntiguo').value = id;
    document.getElementById('cambiarIdEmpleadoAntiguo').value = idEmpleadoAntiguo;
    document.getElementById('cambiarTipo').value = tipo;
    document.getElementById('cambiarNombreAntiguo').value = nombreAntiguo;
    document.getElementById('cambiarBuscarEmpleado').value = '';
    document.getElementById('cambiarIdEmpleadoNuevo').value = '';
    document.getElementById('cambiarEmpleadosListContainer').style.display = 'none';
    document.getElementById('cambiarEmpleadoSeleccionado').style.display = 'none';
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeCambiarEmpleadoModal = function() {
    const modal = document.getElementById('cambiarEmpleadoModal');
    modal.classList.add('closing');
    
    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        currentCambioData = null;
    }, 300);
}

window.buscarEmpleadosParaCambio = async function() {
    const searchTerm = document.getElementById('cambiarBuscarEmpleado').value.trim();
    const container = document.getElementById('cambiarEmpleadosListContainer');
    const list = document.getElementById('cambiarEmpleadosList');
    
    if (searchTerm.length < 2) {
        container.style.display = 'none';
        return;
    }
    
    try {
        const response = await fetch(`/empleados/search?q=${encodeURIComponent(searchTerm)}`);
        const result = await response.json();
        
        if (result.success && result.data && result.data.length > 0) {
            list.innerHTML = result.data.map(emp => `
                <div class="empleado-item" onclick="seleccionarEmpleadoParaCambio(${emp.idEmpleado}, '${emp.nombre}')">
                    <span>${emp.nombre}</span>
                </div>
            `).join('');
            container.style.display = 'block';
        } else {
            list.innerHTML = '<div class="empleado-item-empty">No se encontraron empleados</div>';
            container.style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al buscar empleados', 'error');
    }
}

window.seleccionarEmpleadoParaCambio = function(idEmpleado, nombre) {
    document.getElementById('cambiarIdEmpleadoNuevo').value = idEmpleado;
    document.getElementById('cambiarEmpleadoSeleccionadoNombre').textContent = nombre;
    document.getElementById('cambiarEmpleadoSeleccionado').style.display = 'flex';
    document.getElementById('cambiarBuscarEmpleado').value = '';
    document.getElementById('cambiarEmpleadosListContainer').style.display = 'none';
}

window.limpiarSeleccionEmpleado = function() {
    document.getElementById('cambiarIdEmpleadoNuevo').value = '';
    document.getElementById('cambiarEmpleadoSeleccionado').style.display = 'none';
}

window.saveCambiarEmpleado = async function(event) {
    event.preventDefault();
    
    const idAntiguo = document.getElementById('cambiarIdAntiguo').value;
    const idEmpleadoAntiguo = document.getElementById('cambiarIdEmpleadoAntiguo').value;
    const idEmpleadoNuevo = document.getElementById('cambiarIdEmpleadoNuevo').value;
    const tipo = document.getElementById('cambiarTipo').value;
    
    if (!idEmpleadoNuevo) {
        showToast('Debes seleccionar un nuevo empleado', 'warning');
        return;
    }
    
    if (idEmpleadoAntiguo === idEmpleadoNuevo) {
        showToast('El nuevo empleado debe ser diferente al actual', 'warning');
        return;
    }
    
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        if (!cicloId) {
            showToast('No hay un ciclo seleccionado', 'error');
            return;
        }
        
        const url = tipo === 'supervisor' 
            ? `/zonas/empleados/${idAntiguo}/cambiar-supervisor`
            : `/zonas/representantes/${idAntiguo}/cambiar-representante`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idEmpleadoAntiguo: idEmpleadoAntiguo,
                idEmpleadoNuevo: idEmpleadoNuevo,
                idCiclo: cicloId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            closeCambiarEmpleadoModal();
            
            // Recargar detalles de la zona
            setTimeout(() => {
                viewZoneDetails(currentZoneId);
            }, 500);
        } else {
            showToast(result.message || 'Error al cambiar el empleado', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cambiar el empleado', 'error');
    }
}


// ==========================================
// MODAL DE CONFIRMACIÓN PERSONALIZADO
// ==========================================

let confirmCallback = null;

function showConfirmModal(title, message, question, onConfirm) {
    // Crear modal si no existe
    let modal = document.getElementById('customConfirmModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'customConfirmModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="customConfirmTitle"></h2>
                        <button class="modal-close" onclick="closeCustomConfirmModal()" type="button">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="confirm-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <p class="confirm-message" id="customConfirmMessage"></p>
                        <p class="confirm-question" id="customConfirmQuestion"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeCustomConfirmModal()">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="executeCustomConfirm()">Sí, Continuar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    document.getElementById('customConfirmTitle').textContent = title;
    document.getElementById('customConfirmMessage').textContent = message;
    document.getElementById('customConfirmQuestion').textContent = question;
    
    confirmCallback = onConfirm;
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeCustomConfirmModal = function() {
    const modal = document.getElementById('customConfirmModal');
    if (modal) {
        modal.classList.add('closing');
        setTimeout(() => {
            modal.classList.remove('active', 'closing');
            confirmCallback = null;
        }, 300);
    }
}

window.executeCustomConfirm = function() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeCustomConfirmModal();
}


// ==========================================
// REACTIVAR EMPLEADO CON LICENCIA
// ==========================================

window.confirmReactivarEmpleado = function(idEmpleado, nombreEmpleado) {
    showConfirmModal(
        'Reactivar Empleado',
        `¿Estás seguro de que deseas reactivar a "${nombreEmpleado}"?`,
        'Esta acción finalizará la licencia activa y el empleado volverá a aparecer como activo.',
        async () => {
            await reactivarEmpleadoLicencia(idEmpleado);
        }
    );
}

async function reactivarEmpleadoLicencia(idEmpleado) {
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        if (!cicloId) {
            showToast('No hay un ciclo seleccionado', 'error');
            return;
        }
        
        const response = await fetch(`/zonas/empleados/${idEmpleado}/reactivar-licencia`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idCiclo: cicloId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            
            // Recargar detalles de la zona
            setTimeout(() => {
                viewZoneDetails(currentZoneId);
            }, 500);
        } else {
            showToast(result.message || 'Error al reactivar el empleado', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al reactivar el empleado', 'error');
    }
}


// ==========================================
// UNIFICAR LÍNEA
// ==========================================

let unifyLineasData = [];
let unifySelectedEmpleado = null;

window.openUnificarLineaModal = async function() {
    const modal = document.getElementById('unificarLineaModal');
    
    // Reset
    unifyLineasData = [];
    unifySelectedEmpleado = null;
    document.getElementById('nuevaLineaSelect').value = '';
    document.getElementById('unifyEmpleadoSearch').value = '';
    document.getElementById('unifyEmpleadoList').innerHTML = `
        <div class="geo-empty">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <p>Escribe para buscar empleados...</p>
        </div>
    `;
    document.getElementById('unifyEmpleadoSelected').style.display = 'none';
    
    modal.classList.remove('closing');
    modal.classList.add('active');
    
    // Cargar líneas de la zona
    await loadLineasZona();
    
    // Cargar líneas disponibles (FranqLinea)
    await loadLineasDisponibles();
}

window.closeUnificarLineaModal = function() {
    const modal = document.getElementById('unificarLineaModal');
    modal.classList.add('closing');
    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

async function loadLineasZona() {
    const container = document.getElementById('unifyLineasList');
    
    try {
        const cycleFilter = document.getElementById('cycleFilter');
        const cicloId = cycleFilter ? cycleFilter.value : null;
        
        const url = cicloId
            ? `/zonas/${currentZoneId}/lineas?ciclo=${cicloId}`
            : `/zonas/${currentZoneId}/lineas`;
        
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            unifyLineasData = result.data;
            renderLineasZona();
        } else {
            container.innerHTML = '<p class="error-message">No se pudieron cargar las líneas</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<p class="error-message">Error al cargar las líneas</p>';
    }
}

function renderLineasZona() {
    const container = document.getElementById('unifyLineasList');
    
    if (unifyLineasData.length === 0) {
        container.innerHTML = '<p class="empty-message">No hay líneas para unificar</p>';
        return;
    }
    
    let html = '';
    unifyLineasData.forEach(linea => {
        html += `
            <div class="unify-linea-item">
                <div class="unify-linea-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <div class="unify-linea-content">
                    <div class="unify-linea-name">${linea.linea}</div>
                    <div class="unify-linea-empleado">${linea.empleado || 'Sin empleado'}</div>
                </div>
                <span class="unify-linea-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    ${linea.productos || 0} productos
                </span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

async function loadLineasDisponibles() {
    try {
        const response = await fetch('/api/lineas', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            const select = document.getElementById('nuevaLineaSelect');
            select.innerHTML = '<option value="">Seleccione una línea...</option>';
            
            result.data.forEach(linea => {
                const option = document.createElement('option');
                option.value = linea.idLinea;
                option.textContent = linea.linea;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar líneas disponibles', 'error');
    }
}

let unifySearchTimeout = null;
window.searchEmpleadosUnify = function() {
    clearTimeout(unifySearchTimeout);
    unifySearchTimeout = setTimeout(async () => {
        const searchTerm = document.getElementById('unifyEmpleadoSearch').value.trim();
        
        if (searchTerm.length < 2) {
            document.getElementById('unifyEmpleadoList').innerHTML = `
                <div class="geo-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <p>Escribe al menos 2 caracteres...</p>
                </div>
            `;
            return;
        }
        
        try {
            const response = await fetch(`/api/empleados/search?q=${encodeURIComponent(searchTerm)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const result = await response.json();
            
            if (result.success && result.data && result.data.length > 0) {
                renderEmpleadosUnify(result.data);
            } else {
                document.getElementById('unifyEmpleadoList').innerHTML = '<p class="empty-message">No se encontraron empleados</p>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('unifyEmpleadoList').innerHTML = '<p class="error-message">Error al buscar empleados</p>';
        }
    }, 300);
}

function renderEmpleadosUnify(empleados) {
    const container = document.getElementById('unifyEmpleadoList');
    
    let html = '';
    empleados.forEach(emp => {
        html += `
            <div class="unify-empleado-item" onclick="selectEmpleadoUnify(${emp.idEmpleado}, '${emp.nombre}', '${emp.cargo || 'Empleado'}')">
                <div class="unify-empleado-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="unify-empleado-info">
                    <div class="unify-empleado-name">${emp.nombre}</div>
                    <div class="unify-empleado-cargo">${emp.cargo || 'Empleado'}</div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

window.selectEmpleadoUnify = function(idEmpleado, nombre, cargo) {
    unifySelectedEmpleado = { idEmpleado, nombre, cargo };
    
    document.getElementById('unifyEmpleadoList').style.display = 'none';
    document.getElementById('unifyEmpleadoSelected').style.display = 'flex';
    document.getElementById('unifyEmpleadoSelected').innerHTML = `
        <div class="unify-empleado-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div class="unify-empleado-info" style="flex: 1;">
            <div class="unify-empleado-name">${nombre}</div>
            <div class="unify-empleado-cargo">${cargo}</div>
        </div>
        <span class="unify-empleado-selected-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Seleccionado
        </span>
    `;
}

window.confirmUnificarLinea = function() {
    const nuevaLineaId = document.getElementById('nuevaLineaSelect').value;
    
    if (!nuevaLineaId) {
        showToast('Por favor seleccione la nueva línea', 'warning');
        return;
    }
    
    if (!unifySelectedEmpleado) {
        showToast('Por favor seleccione un empleado', 'warning');
        return;
    }
    
    if (unifyLineasData.length < 2) {
        showToast('Se necesitan al menos 2 líneas para unificar', 'warning');
        return;
    }
    
    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');
    
    title.textContent = 'Confirmar Unificación';
    message.innerHTML = `
        ¿Estás seguro de que deseas unificar las líneas <strong>${unifyLineasData.map(l => l.linea).join(' y ')}</strong>?<br><br>
        <strong>Nueva línea:</strong> ${document.getElementById('nuevaLineaSelect').options[document.getElementById('nuevaLineaSelect').selectedIndex].text}<br>
        <strong>Empleado:</strong> ${unifySelectedEmpleado.nombre}<br><br>
        Esta acción no se puede deshacer.
    `;
    buttonText.textContent = 'Unificar';
    
    pendingAction = {
        type: 'unificarLinea',
        nuevaLineaId: nuevaLineaId,
        empleado: unifySelectedEmpleado,
        lineas: unifyLineasData
    };
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

async function executeUnificarLinea() {
    const data = {
        idZona: currentZoneId,
        idLinea: pendingAction.nuevaLineaId,  // Ahora es idLinea, no idFranqLinea
        idEmpleado: pendingAction.empleado.idEmpleado,
        lineasAUnificar: pendingAction.lineas.map(l => l.idFranqLinea)
    };
    
    try {
        const response = await fetch('/zonas/unificar-linea', {
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
            showToast(result.message, 'success');
            closeUnificarLineaModal();
            // Recargar detalles de la zona
            await viewZoneDetails(currentZoneId);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al unificar líneas', 'error');
    }
}
