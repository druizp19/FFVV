/**
 * ============================================
 * Productos Module - JavaScript
 * ============================================
 */

// ==========================================
// 1. STATE MANAGEMENT
// ==========================================
let searchTimeout = null;

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
// 3. FILTERS
// ==========================================

window.filterProducts = function () {
    const cycleSelect = document.getElementById('cycleFilter');
    const marcaSelect = document.getElementById('marcaFilter');
    const statusSelect = document.getElementById('statusFilter');

    const cycleId = cycleSelect ? cycleSelect.value : '';
    const marcaId = marcaSelect ? marcaSelect.value : '';
    const statusId = statusSelect ? statusSelect.value : '';

    // Obtener los valores actuales de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentCycleId = urlParams.get('ciclo') || '';
    const currentMarcaId = urlParams.get('marca') || '';
    const currentStatusId = urlParams.get('estado') || '';

    // Si los filtros no han cambiado, no hacer nada
    if (cycleId === currentCycleId && marcaId === currentMarcaId && statusId === currentStatusId) {
        return;
    }

    // Construir la nueva URL
    const params = new URLSearchParams();
    if (cycleId) params.append('ciclo', cycleId);
    if (marcaId) params.append('marca', marcaId);
    if (statusId) params.append('estado', statusId);

    const newUrl = params.toString() ? `/productos?${params.toString()}` : '/productos';
    window.location.href = newUrl;
}

window.searchProducts = function () {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#productsTableBody tr');

        let visibleCount = 0;

        rows.forEach(row => {
            const productId = row.getAttribute('data-product-id');
            if (productId) {
                // Buscar en todo el contenido de la fila
                const rowText = row.textContent.toLowerCase();
                const matches = rowText.includes(searchTerm);

                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            }
        });

        // Mostrar mensaje si no hay resultados
        if (visibleCount === 0 && searchTerm) {
            showToast('No se encontraron productos que coincidan con la búsqueda', 'info');
        }
    }, 300);
}

// ==========================================
// 4. INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Productos cargado correctamente');

    // Establecer los filtros desde la URL
    const urlParams = new URLSearchParams(window.location.search);

    const cycleFilter = document.getElementById('cycleFilter');
    if (cycleFilter) {
        const cicloFromUrl = urlParams.get('ciclo');
        if (cicloFromUrl) {
            cycleFilter.value = cicloFromUrl;
        }
    }

    const marcaFilter = document.getElementById('marcaFilter');
    if (marcaFilter) {
        const marcaFromUrl = urlParams.get('marca');
        if (marcaFromUrl) {
            marcaFilter.value = marcaFromUrl;
        }
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        const estadoFromUrl = urlParams.get('estado');
        if (estadoFromUrl) {
            statusFilter.value = estadoFromUrl;
        }
    }
});

// ==========================================
// 5. EDIT PRODUCT
// ==========================================

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

window.editarProducto = async function (productId) {
    try {
        const response = await fetch(`/productos/${productId}`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            const producto = result.data;

            document.getElementById('productId').value = producto.idProducto;

            const marca = producto.marca_mkt?.marca?.marca || 'N/A';
            const mercado = producto.marca_mkt?.mercado?.mercado || 'N/A';
            document.getElementById('productName').value = `${marca} - ${mercado}`;

            document.getElementById('idCuota').value = producto.idCuota || '';
            document.getElementById('idCore').value = producto.idCore || '';
            document.getElementById('idPromocion').value = producto.idPromocion || '';
            document.getElementById('idAlcance').value = producto.idAlcance || '';

            const modal = document.getElementById('editProductModal');
            modal.classList.remove('closing');
            modal.classList.add('active');

            // Prevenir cierre al hacer click en el contenido del modal
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }

            // Cerrar solo al hacer click en el backdrop
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeEditModal();
                }
            });
        } else {
            showToast('No se pudo cargar el producto', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar los datos', 'error');
    }
}

window.closeEditModal = function () {
    const modal = document.getElementById('editProductModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

window.confirmSaveProduct = function (event) {
    event.preventDefault();

    closeEditModal();

    setTimeout(() => {
        const confirmModal = document.getElementById('confirmProductModal');
        confirmModal.classList.remove('closing');
        confirmModal.classList.add('active');

        // Prevenir cierre al hacer click en el contenido
        const modalContent = confirmModal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // Cerrar solo al hacer click en el backdrop
        confirmModal.addEventListener('click', function (e) {
            if (e.target === confirmModal) {
                closeConfirmProductModal();
            }
        });
    }, 300);
}

window.closeConfirmProductModal = function () {
    const modal = document.getElementById('confirmProductModal');
    modal.classList.add('closing');

    setTimeout(() => {
        modal.classList.remove('active', 'closing');
    }, 300);
}

window.saveProduct = async function () {
    const productId = document.getElementById('productId').value;
    const idCuota = document.getElementById('idCuota').value || null;
    const idCore = document.getElementById('idCore').value || null;
    const idPromocion = document.getElementById('idPromocion').value || null;
    const idAlcance = document.getElementById('idAlcance').value || null;

    const data = {
        idCuota: idCuota,
        idCore: idCore,
        idPromocion: idPromocion,
        idAlcance: idAlcance
    };

    try {
        const response = await fetch(`/productos/${productId}`, {
            method: 'PUT',
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
            closeConfirmProductModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar el producto', 'error');
    }
}

// Cerrar modales con ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const confirmModal = document.getElementById('confirmProductModal');
        const editModal = document.getElementById('editProductModal');

        if (confirmModal && confirmModal.classList.contains('active')) {
            closeConfirmProductModal();
        } else if (editModal && editModal.classList.contains('active')) {
            closeEditModal();
        }
    }
});

// Cerrar modales al hacer clic fuera
const editModal = document.getElementById('editProductModal');
if (editModal) {
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) {
            closeEditModal();
        }
    });
}

const confirmModal = document.getElementById('confirmProductModal');
if (confirmModal) {
    confirmModal.addEventListener('click', (e) => {
        if (e.target === confirmModal) {
            closeConfirmProductModal();
        }
    });
}
