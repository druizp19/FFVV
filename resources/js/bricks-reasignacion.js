/**
 * ============================================
 * Bricks Reasignación Module - JavaScript
 * ============================================
 */

// State
let geosegmentoOrigenId = null;
let bricksSeleccionados = [];
let allBricks = [];

// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ==========================================
// TOAST NOTIFICATIONS
// ==========================================

const toastIcons = {
    success: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
    error: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
};

window.showToast = function (message, type = 'info', duration = 5000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const toastId = 'toast_' + Date.now();
    toast.id = toastId;

    toast.innerHTML = `
        <div class="toast-icon">${toastIcons[type] || toastIcons.info}</div>
        <div class="toast-content">
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
// MODAL MANAGEMENT
// ==========================================

window.openReasignarBricksModal = function () {
    // Verificar si el botón está deshabilitado (ciclo cerrado)
    const btnReasignar = document.getElementById('btnReasignarBricks');
    if (btnReasignar && btnReasignar.disabled) {
        showToast('No se pueden reasignar bricks en un ciclo cerrado. Por favor, selecciona el ciclo activo.', 'warning', 4000);
        return;
    }

    const modal = document.getElementById('reasignarBricksModal');
    
    // Reset state
    geosegmentoOrigenId = null;
    bricksSeleccionados = [];
    allBricks = [];
    
    // Reset form
    document.getElementById('geosegmentoOrigenModal').value = '';
    document.getElementById('geosegmentoDestinoModal').value = '';
    document.getElementById('geosegmentoDestinoModal').innerHTML = '<option value="">Seleccionar...</option>';
    document.getElementById('bricksListModal').innerHTML = `
        <div class="brick-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            <p>Selecciona un geosegmento origen</p>
        </div>
    `;
    
    updateSelectedCountModal();
    loadGeosegmentosOrigen();
    
    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeReasignarBricksModal = function () {
    const modal = document.getElementById('reasignarBricksModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        geosegmentoOrigenId = null;
        bricksSeleccionados = [];
        allBricks = [];
    }, 300);
}

// ==========================================
// LOAD DATA
// ==========================================

async function loadGeosegmentosOrigen() {
    try {
        const response = await fetch('/bricks/reasignacion/get-bricks?idGeosegmento=0');
        
        // Cargar geosegmentos que tienen bricks
        const geoResponse = await fetch('/api/geosegmentos');
        const result = await geoResponse.json();
        
        const select = document.getElementById('geosegmentoOrigenModal');
        select.innerHTML = '<option value="">Seleccionar...</option>';
        
        if (result.success && result.data) {
            result.data.forEach(geo => {
                const option = document.createElement('option');
                option.value = geo.idGeosegmento;
                option.textContent = geo.geosegmento;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar geosegmentos', 'error');
    }
}

// Event listener para cambio de geosegmento origen
document.addEventListener('DOMContentLoaded', function() {
    const geosegmentoOrigenSelect = document.getElementById('geosegmentoOrigenModal');
    if (geosegmentoOrigenSelect) {
        geosegmentoOrigenSelect.addEventListener('change', async function() {
            geosegmentoOrigenId = this.value;
            
            if (geosegmentoOrigenId) {
                await loadBricksGeosegmento(geosegmentoOrigenId);
                await loadGeosegmentosDestino(geosegmentoOrigenId);
            } else {
                document.getElementById('bricksListModal').innerHTML = `
                    <div class="brick-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                        <p>Selecciona un geosegmento origen</p>
                    </div>
                `;
                document.getElementById('geosegmentoDestinoModal').innerHTML = '<option value="">Seleccionar...</option>';
            }
        });
    }

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllBricksModal');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.brick-checkbox-modal');
            checkboxes.forEach(cb => cb.checked = this.checked);
            actualizarBricksSeleccionados();
        });
    }

    // Cerrar modales con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const reasignarModal = document.getElementById('reasignarBricksModal');
            const confirmModal = document.getElementById('confirmModal');
            
            if (confirmModal && confirmModal.classList.contains('active')) {
                closeConfirmModal();
            } else if (reasignarModal && reasignarModal.classList.contains('active')) {
                closeReasignarBricksModal();
            }
        }
    });
    
    // Cerrar modal de reasignación al hacer clic fuera
    const reasignarModal = document.getElementById('reasignarBricksModal');
    if (reasignarModal) {
        reasignarModal.addEventListener('click', (e) => {
            if (e.target === reasignarModal) {
                closeReasignarBricksModal();
            }
        });
    }

    // Cerrar modal de confirmación al hacer clic fuera
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                closeConfirmModal();
            }
        });
    }
});

async function loadBricksGeosegmento(idGeosegmento) {
    const container = document.getElementById('bricksListModal');
    container.innerHTML = `
        <div class="brick-loading">
            <div class="spinner"></div>
            <p>Cargando bricks...</p>
        </div>
    `;

    try {
        const response = await fetch(`/bricks/reasignacion/get-bricks?idGeosegmento=${idGeosegmento}`);
        const data = await response.json();
        
        if (data.success && data.bricks && data.bricks.length > 0) {
            allBricks = data.bricks;
            renderBricksListModal(data.bricks);
        } else {
            container.innerHTML = `
                <div class="brick-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 8v4M12 16h.01"></path>
                    </svg>
                    <p>No hay bricks disponibles en este geosegmento</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar los bricks', 'error');
        container.innerHTML = `
            <div class="brick-empty error">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <p>Error al cargar bricks</p>
            </div>
        `;
    }
}

function renderBricksListModal(bricks) {
    const container = document.getElementById('bricksListModal');
    
    let html = '<div class="bricks-list-items">';
    
    bricks.forEach(brick => {
        const location = [brick.departamento, brick.provincia, brick.distrito].filter(Boolean).join(', ');
        
        html += `
            <label class="brick-item-compact">
                <input type="checkbox" class="checkbox-input brick-checkbox-modal" value="${brick.idbrick}" data-id="${brick.idbrick}">
                <span class="checkbox-custom"></span>
                <div class="brick-item-info">
                    <div class="brick-item-name">${brick.descripcionBrick || 'N/A'}</div>
                    ${brick.codigoBrick ? `<div class="brick-item-code">${brick.codigoBrick}</div>` : ''}
                    ${location ? `<div class="brick-item-location">${location}</div>` : ''}
                </div>
            </label>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;

    // Agregar event listeners a los checkboxes
    document.querySelectorAll('.brick-checkbox-modal').forEach(cb => {
        cb.addEventListener('change', actualizarBricksSeleccionados);
    });
}

async function loadGeosegmentosDestino(idGeosegmentoOrigen) {
    try {
        const response = await fetch(`/bricks/reasignacion/get-destinos?idGeosegmentoOrigen=${idGeosegmentoOrigen}`);
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('geosegmentoDestinoModal');
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.geosegmentos.forEach(geo => {
                select.innerHTML += `<option value="${geo.idGeosegmento}">${geo.geosegmento}</option>`;
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function actualizarBricksSeleccionados() {
    bricksSeleccionados = [];
    document.querySelectorAll('.brick-checkbox-modal:checked').forEach(cb => {
        bricksSeleccionados.push(parseInt(cb.value));
    });
    updateSelectedCountModal();

    // Actualizar estado del checkbox "seleccionar todos"
    const totalBricks = document.querySelectorAll('.brick-checkbox-modal').length;
    const bricksChecked = bricksSeleccionados.length;
    const selectAllCheckbox = document.getElementById('selectAllBricksModal');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = totalBricks === bricksChecked && totalBricks > 0;
    }
}

function updateSelectedCountModal() {
    const countElement = document.getElementById('selectedBricksCountModal');
    if (countElement) {
        countElement.textContent = bricksSeleccionados.length;
    }
}

// ==========================================
// REASIGNAR BRICKS
// ==========================================

window.confirmReasignarBricks = function () {
    const geosegmentoDestino = document.getElementById('geosegmentoDestinoModal').value;
    
    if (!geosegmentoOrigenId) {
        showToast('Debes seleccionar un geosegmento origen', 'warning');
        return;
    }

    if (!geosegmentoDestino) {
        showToast('Debes seleccionar un geosegmento destino', 'warning');
        return;
    }

    if (bricksSeleccionados.length === 0) {
        showToast('Debes seleccionar al menos un brick para reasignar', 'warning');
        return;
    }

    // Obtener nombres de los geosegmentos
    const geosegmentoOrigenSelect = document.getElementById('geosegmentoOrigenModal');
    const geosegmentoDestinoSelect = document.getElementById('geosegmentoDestinoModal');
    const nombreOrigen = geosegmentoOrigenSelect.options[geosegmentoOrigenSelect.selectedIndex].text;
    const nombreDestino = geosegmentoDestinoSelect.options[geosegmentoDestinoSelect.selectedIndex].text;

    // Mostrar modal de confirmación
    showConfirmModal(
        '¿Confirmar reasignación?',
        `Se reasignarán <strong>${bricksSeleccionados.length}</strong> brick(s) de <strong>${nombreOrigen}</strong> a <strong>${nombreDestino}</strong>.<br><br><small style="color: var(--text-muted);">Si el geosegmento origen se queda sin bricks, será desactivado automáticamente.</small>`,
        () => ejecutarReasignacion(geosegmentoDestino)
    );
}

async function ejecutarReasignacion(geosegmentoDestino) {
    const reasignarBtn = document.querySelector('#reasignarBricksModal .modal-footer .btn-primary');
    const originalText = reasignarBtn.innerHTML;
    reasignarBtn.disabled = true;
    reasignarBtn.innerHTML = `
        <div class="spinner" style="width: 16px; height: 16px; border-width: 2px;"></div>
        <span>Reasignando...</span>
    `;

    try {
        const response = await fetch('/bricks/reasignacion/reasignar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idGeosegmentoOrigen: geosegmentoOrigenId,
                idGeosegmentoDestino: geosegmentoDestino,
                bricks: bricksSeleccionados
            })
        });

        const data = await response.json();

        reasignarBtn.disabled = false;
        reasignarBtn.innerHTML = originalText;

        if (data.success) {
            showToast(data.message, 'success');
            closeReasignarBricksModal();
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        reasignarBtn.disabled = false;
        reasignarBtn.innerHTML = originalText;
        showToast('Error al reasignar los bricks', 'error');
    }
}

// ==========================================
// CONFIRM MODAL
// ==========================================

let confirmCallback = null;

window.showConfirmModal = function (title, message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    const titleElement = document.getElementById('confirmModalTitle');
    const messageElement = document.getElementById('confirmModalMessage');
    const confirmButton = document.getElementById('confirmModalButton');

    titleElement.textContent = title;
    messageElement.innerHTML = message;
    confirmCallback = onConfirm;

    // Remover event listeners anteriores
    const newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);

    // Agregar nuevo event listener
    document.getElementById('confirmModalButton').addEventListener('click', function() {
        if (confirmCallback) {
            confirmCallback();
        }
        closeConfirmModal();
    });

    modal.classList.remove('closing');
    modal.classList.add('active');
}

window.closeConfirmModal = function () {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        confirmCallback = null;
    }, 300);
}

// ==========================================
// FILTERS
// ==========================================

window.applyFilters = function () {
    const ciclo = document.getElementById('cicloFilter').value;
    const departamento = document.getElementById('departamentoFilter').value;
    const search = document.getElementById('searchInput').value;
    
    const url = new URL(window.location.href);
    
    if (ciclo) {
        url.searchParams.set('ciclo', ciclo);
    } else {
        url.searchParams.delete('ciclo');
    }
    
    if (departamento) {
        url.searchParams.set('departamento', departamento);
    } else {
        url.searchParams.delete('departamento');
    }
    
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// ==========================================
// SEARCH BRICKS
// ==========================================

window.searchBricks = function () {
    let searchTimeout;
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}
