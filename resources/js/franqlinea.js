/**
 * ============================================
 * FranqLinea Module - JavaScript
 * ============================================
 */

// ==========================================
// 1. STATE MANAGEMENT
// ==========================================
let pendingAction = null;
let searchTimeout = null;
let createdMixtas = [];
let createdLineas = [];
let selectedMixta = null;
let selectedLinea = null;

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

// ==========================================
// 3. MODAL MANAGEMENT
// ==========================================

window.openCreateModal = function () {
    const modal = document.getElementById('createModal');
    const form = document.getElementById('createForm');
    form.reset();
    
    // Reset state
    createdMixtas = [];
    createdLineas = [];
    selectedMixta = null;
    selectedLinea = null;
    
    // Reset UI
    document.getElementById('mixtasList').innerHTML = '';
    document.getElementById('lineasList').innerHTML = '';
    document.getElementById('newMixtaInput').style.display = 'none';
    document.getElementById('newLineaInput').style.display = 'none';
    document.getElementById('mixtaSelect').value = '';
    document.getElementById('lineaSelect').value = '';
    
    // Cargar mixtas y líneas existentes
    loadExistingMixtas();
    loadExistingLineas();
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeCreateModal = function () {
    const modal = document.getElementById('createModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

window.closeConfirmModal = function () {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

// ==========================================
// 4. FRANQLINEA CRUD OPERATIONS
// ==========================================

window.saveFranqLinea = async function (event) {
    event.preventDefault();

    // Validar que se haya seleccionado una mixta
    if (!selectedMixta) {
        showToast('Por favor seleccione o cree una Mixta', 'warning');
        return;
    }

    // Validar que se haya seleccionado una línea
    if (!selectedLinea) {
        showToast('Por favor seleccione o cree una Línea', 'warning');
        return;
    }

    const idEstructura = document.getElementById('idEstructura').value;
    if (!idEstructura) {
        showToast('Por favor seleccione una Estructura', 'warning');
        return;
    }

    const data = {
        mixta: selectedMixta.name,
        linea: selectedLinea.name,
        idEstructura: idEstructura
    };

    try {
        const response = await fetch('/franqlinea', {
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
            closeCreateModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar FranqLinea', 'error');
    }
}

window.toggleEstado = function (id, estadoActual) {
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
    const accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';

    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const title = document.getElementById('confirmTitle');
    const buttonText = document.getElementById('confirmButtonText');

    title.textContent = 'Confirmar Cambio de Estado';
    message.textContent = `¿Estás seguro de que deseas ${accion} esta FranqLinea?`;
    buttonText.textContent = nuevoEstado === 'Activo' ? 'Activar' : 'Desactivar';

    pendingAction = {
        type: 'toggleEstado',
        id: id,
        nuevoEstado: nuevoEstado
    };

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.executeConfirmAction = async function () {
    if (!pendingAction) return;

    if (pendingAction.type === 'toggleEstado') {
        await executeToggleEstado(pendingAction.id, pendingAction.nuevoEstado);
    }

    closeConfirmModal();
    pendingAction = null;
}

async function executeToggleEstado(id, nuevoEstado) {
    const idEstado = nuevoEstado === 'Activo' ? 1 : 0;

    try {
        const response = await fetch(`/franqlinea/${id}/estado`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ idEstado: idEstado })
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cambiar estado', 'error');
    }
}

// ==========================================
// 5. SEARCH FUNCTIONALITY
// ==========================================

window.searchFranqLineas = function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = document.getElementById('searchInput').value.trim();
        const url = new URL(window.location.href);
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        window.location.href = url.toString();
    }, 500);
}

// ==========================================
// 6. INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('FranqLinea module loaded');
});


// ==========================================
// 7. GESTIÓN DE MIXTAS Y LÍNEAS
// ==========================================

// Cargar Mixtas Existentes
async function loadExistingMixtas() {
    try {
        const response = await fetch('/api/mixtas', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            const select = document.getElementById('mixtaSelect');
            // Limpiar opciones excepto las primeras dos
            while (select.options.length > 2) {
                select.remove(2);
            }
            
            // Agregar mixtas existentes
            result.data.forEach(mixta => {
                const option = document.createElement('option');
                option.value = mixta.idMixta;
                option.textContent = mixta.mixta;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar mixtas:', error);
    }
}

// Cargar Líneas Existentes
async function loadExistingLineas() {
    try {
        const response = await fetch('/api/lineas', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            const select = document.getElementById('lineaSelect');
            // Limpiar opciones excepto las primeras dos
            while (select.options.length > 2) {
                select.remove(2);
            }
            
            // Agregar líneas existentes
            result.data.forEach(linea => {
                const option = document.createElement('option');
                option.value = linea.idLinea;
                option.textContent = linea.linea;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar líneas:', error);
    }
}

// Handle Mixta Select Change
window.handleMixtaSelectChange = function() {
    const select = document.getElementById('mixtaSelect');
    const newInput = document.getElementById('newMixtaInput');
    
    if (select.value === '__new__') {
        newInput.style.display = 'block';
        document.getElementById('newMixta').focus();
    } else if (select.value) {
        newInput.style.display = 'none';
        selectedMixta = {
            id: select.value,
            name: select.options[select.selectedIndex].text,
            isExisting: true
        };
        renderMixtasList();
    } else {
        newInput.style.display = 'none';
    }
}

// Handle Línea Select Change
window.handleLineaSelectChange = function() {
    const select = document.getElementById('lineaSelect');
    const newInput = document.getElementById('newLineaInput');
    
    if (select.value === '__new__') {
        newInput.style.display = 'block';
        document.getElementById('newLinea').focus();
    } else if (select.value) {
        newInput.style.display = 'none';
        selectedLinea = {
            id: select.value,
            name: select.options[select.selectedIndex].text,
            isExisting: true
        };
        renderLineasList();
    } else {
        newInput.style.display = 'none';
    }
}

// Crear Nueva Mixta
window.createMixta = function() {
    const input = document.getElementById('newMixta');
    const mixtaName = input.value.trim();
    
    if (!mixtaName) {
        showToast('Por favor ingrese un nombre para la mixta', 'warning');
        return;
    }
    
    // Verificar si ya existe
    if (createdMixtas.some(m => m.name.toLowerCase() === mixtaName.toLowerCase())) {
        showToast('Esta mixta ya fue agregada', 'warning');
        return;
    }
    
    // Agregar a la lista
    const newMixta = {
        id: 'new_' + Date.now(),
        name: mixtaName,
        isExisting: false
    };
    
    createdMixtas.push(newMixta);
    selectedMixta = newMixta;
    
    // Reset
    input.value = '';
    document.getElementById('newMixtaInput').style.display = 'none';
    document.getElementById('mixtaSelect').value = '';
    
    renderMixtasList();
    showToast('Mixta agregada correctamente', 'success');
}

// Crear Nueva Línea
window.createLinea = function() {
    const input = document.getElementById('newLinea');
    const lineaName = input.value.trim();
    
    if (!lineaName) {
        showToast('Por favor ingrese un nombre para la línea', 'warning');
        return;
    }
    
    // Verificar si ya existe
    if (createdLineas.some(l => l.name.toLowerCase() === lineaName.toLowerCase())) {
        showToast('Esta línea ya fue agregada', 'warning');
        return;
    }
    
    // Agregar a la lista
    const newLinea = {
        id: 'new_' + Date.now(),
        name: lineaName,
        isExisting: false
    };
    
    createdLineas.push(newLinea);
    selectedLinea = newLinea;
    
    // Reset
    input.value = '';
    document.getElementById('newLineaInput').style.display = 'none';
    document.getElementById('lineaSelect').value = '';
    
    renderLineasList();
    showToast('Línea agregada correctamente', 'success');
}

// Cancelar Nueva Mixta
window.cancelNewMixta = function() {
    document.getElementById('newMixtaInput').style.display = 'none';
    document.getElementById('newMixta').value = '';
    document.getElementById('mixtaSelect').value = '';
}

// Cancelar Nueva Línea
window.cancelNewLinea = function() {
    document.getElementById('newLineaInput').style.display = 'none';
    document.getElementById('newLinea').value = '';
    document.getElementById('lineaSelect').value = '';
}

// Render Lista de Mixtas
function renderMixtasList() {
    const container = document.getElementById('mixtasList');
    
    if (createdMixtas.length === 0 && !selectedMixta) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Mostrar mixta seleccionada si es existente
    if (selectedMixta && selectedMixta.isExisting) {
        html += `
            <div class="created-item selected">
                <div class="created-item-content">
                    <div class="created-item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="created-item-text">${selectedMixta.name}</span>
                    <span class="created-item-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"></path>
                        </svg>
                        Seleccionada
                    </span>
                </div>
            </div>
        `;
    }
    
    // Mostrar mixtas creadas
    createdMixtas.forEach(mixta => {
        const isSelected = selectedMixta && selectedMixta.id === mixta.id;
        html += `
            <div class="created-item ${isSelected ? 'selected' : ''}" onclick="selectMixta('${mixta.id}')">
                <div class="created-item-content">
                    <div class="created-item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="created-item-text">${mixta.name}</span>
                    ${isSelected ? `
                        <span class="created-item-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4"></path>
                            </svg>
                            Seleccionada
                        </span>
                    ` : ''}
                </div>
                <button type="button" class="created-item-remove" onclick="event.stopPropagation(); removeMixta('${mixta.id}')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Render Lista de Líneas
function renderLineasList() {
    const container = document.getElementById('lineasList');
    
    if (createdLineas.length === 0 && !selectedLinea) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Mostrar línea seleccionada si es existente
    if (selectedLinea && selectedLinea.isExisting) {
        html += `
            <div class="created-item selected">
                <div class="created-item-content">
                    <div class="created-item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="created-item-text">${selectedLinea.name}</span>
                    <span class="created-item-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"></path>
                        </svg>
                        Seleccionada
                    </span>
                </div>
            </div>
        `;
    }
    
    // Mostrar líneas creadas
    createdLineas.forEach(linea => {
        const isSelected = selectedLinea && selectedLinea.id === linea.id;
        html += `
            <div class="created-item ${isSelected ? 'selected' : ''}" onclick="selectLinea('${linea.id}')">
                <div class="created-item-content">
                    <div class="created-item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="created-item-text">${linea.name}</span>
                    ${isSelected ? `
                        <span class="created-item-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4"></path>
                            </svg>
                            Seleccionada
                        </span>
                    ` : ''}
                </div>
                <button type="button" class="created-item-remove" onclick="event.stopPropagation(); removeLinea('${linea.id}')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Seleccionar Mixta
window.selectMixta = function(mixtaId) {
    const mixta = createdMixtas.find(m => m.id === mixtaId);
    if (mixta) {
        selectedMixta = mixta;
        renderMixtasList();
    }
}

// Seleccionar Línea
window.selectLinea = function(lineaId) {
    const linea = createdLineas.find(l => l.id === lineaId);
    if (linea) {
        selectedLinea = linea;
        renderLineasList();
    }
}

// Remover Mixta
window.removeMixta = function(mixtaId) {
    createdMixtas = createdMixtas.filter(m => m.id !== mixtaId);
    if (selectedMixta && selectedMixta.id === mixtaId) {
        selectedMixta = createdMixtas.length > 0 ? createdMixtas[0] : null;
    }
    renderMixtasList();
    showToast('Mixta eliminada', 'info');
}

// Remover Línea
window.removeLinea = function(lineaId) {
    createdLineas = createdLineas.filter(l => l.id !== lineaId);
    if (selectedLinea && selectedLinea.id === lineaId) {
        selectedLinea = createdLineas.length > 0 ? createdLineas[0] : null;
    }
    renderLineasList();
    showToast('Línea eliminada', 'info');
}