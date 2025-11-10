/**
 * ============================================
 * Geosegmentos Module - JavaScript
 * ============================================
 */

// State
let currentGeosegmentoId = null;
let currentGeosegmentoName = null;
let selectedUbigeos = [];
let searchTimeout = null;

// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ==========================================
// TOAST NOTIFICATIONS (copiado de zonas.js)
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
// MODAL MANAGEMENT
// ==========================================

window.openCreateGeosegmentoModal = function () {
    const modal = document.getElementById('geosegmentoModal');
    const title = document.getElementById('geosegmentoModalTitle');
    const form = document.getElementById('geosegmentoForm');
    
    title.textContent = 'Nuevo Geosegmento';
    form.reset();
    document.getElementById('geosegmentoId').value = '';
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.openEditGeosegmentoModal = async function (id) {
    const modal = document.getElementById('geosegmentoModal');
    const title = document.getElementById('geosegmentoModalTitle');
    
    title.textContent = 'Editar Geosegmento';
    document.getElementById('geosegmentoId').value = id;
    
    // Cargar datos
    try {
        const response = await fetch(`/geosegmentos/${id}`);
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('geosegmentoNombre').value = result.data.geosegmento;
            document.getElementById('geosegmentoLugar').value = result.data.lugar || '';
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar el geosegmento', 'error');
    }
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeGeosegmentoModal = function () {
    const modal = document.getElementById('geosegmentoModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

window.saveGeosegmento = async function (event) {
    event.preventDefault();

    const id = document.getElementById('geosegmentoId').value;
    const geosegmento = document.getElementById('geosegmentoNombre').value.trim();
    const lugar = document.getElementById('geosegmentoLugar').value.trim();

    if (!geosegmento) {
        showToast('El nombre del geosegmento es requerido', 'warning');
        return;
    }

    const data = { geosegmento };
    if (lugar) data.lugar = lugar;

    const url = id ? `/geosegmentos/${id}` : '/geosegmentos';
    const method = id ? 'PUT' : 'POST';

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
            closeGeosegmentoModal();
            
            if (!id && result.data) {
                // Nuevo geosegmento creado
                showToastWithAction(
                    `Geosegmento "${result.data.geosegmento}" creado exitosamente`,
                    'success',
                    '¿Deseas agregar ubigeos ahora?',
                    'Agregar Ubigeos',
                    () => openAssignUbigeosModal(result.data.idGeosegmento, result.data.geosegmento)
                );
            } else {
                showToast(result.message, 'success');
            }
            
            // Recargar tabla
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar el geosegmento', 'error');
    }
}

// ==========================================
// ASIGNAR UBIGEOS
// ==========================================

window.openAssignUbigeosModal = function (geoId, geoName) {
    currentGeosegmentoId = geoId;
    currentGeosegmentoName = geoName;
    selectedUbigeos = [];
    
    const modal = document.getElementById('assignUbigeosModal');
    const title = document.getElementById('assignUbigeosTitle');
    const searchInput = document.getElementById('ubigeoSearchInput');
    
    title.textContent = `Agregar Ubigeos a "${geoName}"`;
    searchInput.value = '';
    
    document.getElementById('ubigeosList').innerHTML = `
        <div class="ubigeo-empty">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <p>Escribe para buscar ubigeos...</p>
        </div>
    `;
    
    updateSelectedCount();
    
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
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        loadAvailableUbigeos();
    }, 300);
}

async function loadAvailableUbigeos() {
    const searchTerm = document.getElementById('ubigeoSearchInput').value.toLowerCase().trim();
    const container = document.getElementById('ubigeosList');
    
    if (!searchTerm || searchTerm.length < 2) {
        container.innerHTML = `
            <div class="ubigeo-empty">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>Escribe al menos 2 caracteres para buscar...</p>
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
    
    try {
        const url = `/api/ubigeos/search?q=${encodeURIComponent(searchTerm)}&geosegmento=${currentGeosegmentoId || ''}`;
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success && result.data && result.data.length > 0) {
            renderUbigeosList(result.data);
        } else {
            container.innerHTML = `
                <div class="ubigeo-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 8v4M12 16h.01"></path>
                    </svg>
                    <p>No se encontraron ubigeos con "${searchTerm}"</p>
                    ${result.message ? `<small style="color: var(--text-muted); margin-top: 8px; display: block;">${result.message}</small>` : ''}
                </div>
            `;
        }
    } catch (error) {
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
    }
}

function renderUbigeosList(ubigeos) {
    const container = document.getElementById('ubigeosList');
    
    // Agrupar por departamento y provincia
    const grouped = {};
    ubigeos.forEach(ubigeo => {
        if (!grouped[ubigeo.departamento]) {
            grouped[ubigeo.departamento] = {};
        }
        if (!grouped[ubigeo.departamento][ubigeo.provincia]) {
            grouped[ubigeo.departamento][ubigeo.provincia] = [];
        }
        grouped[ubigeo.departamento][ubigeo.provincia].push(ubigeo);
    });
    
    let html = '';
    
    // Renderizar por departamento
    Object.keys(grouped).sort().forEach(departamento => {
        const provincias = grouped[departamento];
        const totalDep = Object.values(provincias).flat().length;
        const depIds = Object.values(provincias).flat().map(u => u.idUbigeo);
        const allDepSelected = depIds.every(id => selectedUbigeos.includes(id));
        
        html += `
            <div class="ubigeo-group">
                <div class="ubigeo-group-header" onclick="toggleGroup('dep', ${JSON.stringify(depIds).replace(/"/g, '&quot;')})">
                    <div class="ubigeo-group-check ${allDepSelected ? 'checked' : ''}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="ubigeo-group-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ubigeo-group-title">
                        <strong>${departamento}</strong>
                        <span class="ubigeo-count">${totalDep} ubigeos</span>
                    </div>
                    <div class="ubigeo-group-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="ubigeo-group-content">
        `;
        
        // Renderizar por provincia
        Object.keys(provincias).sort().forEach(provincia => {
            const distritos = provincias[provincia];
            const provIds = distritos.map(u => u.idUbigeo);
            const allProvSelected = provIds.every(id => selectedUbigeos.includes(id));
            
            html += `
                <div class="ubigeo-subgroup">
                    <div class="ubigeo-subgroup-header" onclick="toggleGroup('prov', ${JSON.stringify(provIds).replace(/"/g, '&quot;')})">
                        <div class="ubigeo-group-check ${allProvSelected ? 'checked' : ''}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <strong>${provincia}</strong>
                        <span class="ubigeo-count">${distritos.length}</span>
                    </div>
                    <div class="ubigeo-subgroup-content">
            `;
            
            // Renderizar distritos
            distritos.forEach(ubigeo => {
                html += `
                    <div class="ubigeo-item-compact ${selectedUbigeos.includes(ubigeo.idUbigeo) ? 'selected' : ''}" 
                         onclick="toggleUbigeo(${ubigeo.idUbigeo})">
                        <div class="ubigeo-item-check">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <span>${ubigeo.distrito}</span>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

window.toggleGroup = function(type, ids) {
    event.stopPropagation();
    
    const allSelected = ids.every(id => selectedUbigeos.includes(id));
    
    if (allSelected) {
        // Deseleccionar todos
        ids.forEach(id => {
            const index = selectedUbigeos.indexOf(id);
            if (index > -1) {
                selectedUbigeos.splice(index, 1);
            }
        });
    } else {
        // Seleccionar todos
        ids.forEach(id => {
            if (!selectedUbigeos.includes(id)) {
                selectedUbigeos.push(id);
            }
        });
    }
    
    // Re-renderizar
    const searchTerm = document.getElementById('ubigeoSearchInput').value.toLowerCase().trim();
    if (searchTerm.length >= 2) {
        loadAvailableUbigeos();
    }
    
    updateSelectedCount();
}

window.toggleUbigeo = function (ubigeoId) {
    event.stopPropagation();
    
    const index = selectedUbigeos.indexOf(ubigeoId);
    
    if (index > -1) {
        selectedUbigeos.splice(index, 1);
    } else {
        selectedUbigeos.push(ubigeoId);
    }
    
    // Re-renderizar para actualizar el estado visual
    const searchTerm = document.getElementById('ubigeoSearchInput').value.toLowerCase().trim();
    if (searchTerm.length >= 2) {
        loadAvailableUbigeos();
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
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(result.message || 'Error al asignar ubigeos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al asignar ubigeos', 'error');
    }
}

// ==========================================
// VER DETALLES
// ==========================================

window.viewGeosegmentoDetails = async function (id) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('detailsContent');
    const title = document.getElementById('detailsTitle');
    
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Cargando ubigeos...</p>
        </div>
    `;
    
    modal.classList.remove('closing');
    modal.classList.add('active');
    
    try {
        const response = await fetch(`/geosegmentos/${id}/ubigeos-list`);
        const result = await response.json();
        
        if (result.success) {
            title.textContent = `Ubigeos de "${result.geosegmento}" (${result.ubigeos.length})`;
            
            if (result.ubigeos.length > 0) {
                content.innerHTML = `
                    <div class="modal-body">
                        <div class="ubigeos-table-wrapper">
                            <table class="ubigeos-detail-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Departamento</th>
                                        <th>Provincia</th>
                                        <th>Distrito</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${result.ubigeos.map(u => `
                                        <tr>
                                            <td><span class="ubigeo-code">${u.ubigeo || '-'}</span></td>
                                            <td>${u.departamento}</td>
                                            <td>${u.provincia}</td>
                                            <td><strong>${u.distrito}</strong></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="modal-body">
                        <div class="ubigeo-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 8v4M12 16h.01"></path>
                            </svg>
                            <p>Este geosegmento no tiene ubigeos asignados</p>
                        </div>
                    </div>
                `;
            }
        } else {
            content.innerHTML = `<p class="error-message">Error al cargar los ubigeos</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = `<p class="error-message">Error al cargar los ubigeos</p>`;
    }
}

window.closeDetailsModal = function () {
    const modal = document.getElementById('detailsModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

// ==========================================
// FILTERS
// ==========================================

window.changeCiclo = function () {
    applyFilters();
}

window.filterGeosegmentos = function () {
    applyFilters();
}

window.searchGeosegmentos = function () {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const filter = document.getElementById('ubigeoFilter').value;
    const ciclo = document.getElementById('cicloFilter').value;
    
    // Construir URL con parámetros
    const url = new URL(window.location.href);
    url.searchParams.delete('page'); // Reset pagination
    
    if (ciclo) {
        url.searchParams.set('ciclo', ciclo);
    } else {
        url.searchParams.delete('ciclo');
    }
    
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    
    if (filter) {
        url.searchParams.set('filter', filter);
    } else {
        url.searchParams.delete('filter');
    }
    
    // Recargar página con nuevos parámetros
    window.location.href = url.toString();
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Geosegmentos cargado correctamente');
    
    // Cerrar modales con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const assignModal = document.getElementById('assignUbigeosModal');
            const geoModal = document.getElementById('geosegmentoModal');
            const detailsModal = document.getElementById('detailsModal');
            
            if (assignModal && assignModal.classList.contains('active')) {
                closeAssignUbigeosModal();
            } else if (geoModal && geoModal.classList.contains('active')) {
                closeGeosegmentoModal();
            } else if (detailsModal && detailsModal.classList.contains('active')) {
                closeDetailsModal();
            }
        }
    });
    
    // Cerrar modales al hacer clic fuera
    const modals = ['geosegmentoModal', 'assignUbigeosModal', 'detailsModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    if (modalId === 'geosegmentoModal') closeGeosegmentoModal();
                    else if (modalId === 'assignUbigeosModal') closeAssignUbigeosModal();
                    else if (modalId === 'detailsModal') closeDetailsModal();
                }
            });
        }
    });
});
