/**
 * ============================================
 * Empleados Module - JavaScript
 * ============================================
 */

let searchTimeout = null;

// ==========================================
// SEARCH & FILTERS
// ==========================================

window.searchEmployees = function() {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#employeesTableBody tr');

        rows.forEach(row => {
            const employeeName = row.getAttribute('data-employee-name');
            const employeeDni = row.getAttribute('data-employee-dni');
            
            if (employeeName && employeeDni) {
                const matches = employeeName.toLowerCase().includes(searchTerm) || 
                               employeeDni.toLowerCase().includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            }
        });
    }, 300);
}

window.applyFilters = function() {
    const cargo = document.getElementById('filterCargo').value;
    const area = document.getElementById('filterArea').value;
    const search = document.getElementById('searchInput').value;

    // Construir URL con parámetros
    const params = new URLSearchParams();
    if (cargo) params.append('cargo', cargo);
    if (area) params.append('area', area);
    if (search) params.append('search', search);

    // Recargar página con filtros
    const url = params.toString() ? `/empleados?${params.toString()}` : '/empleados';
    window.location.href = url;
}

window.clearFilters = function() {
    window.location.href = '/empleados';
}

// ==========================================
// TOAST NOTIFICATIONS
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
    info: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
    </svg>`
};

const toastTitles = {
    success: '¡Éxito!',
    error: 'Error',
    info: 'Información'
};

window.showToast = function(message, type = 'info', title = null, duration = 5000) {
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

window.closeToast = function(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Empleados cargado correctamente');

    // Establecer valores de filtros desde la URL
    const urlParams = new URLSearchParams(window.location.search);
    
    const cargoFilter = document.getElementById('filterCargo');
    const areaFilter = document.getElementById('filterArea');
    const searchInput = document.getElementById('searchInput');

    if (cargoFilter && urlParams.get('cargo')) {
        cargoFilter.value = urlParams.get('cargo');
    }

    if (areaFilter && urlParams.get('area')) {
        areaFilter.value = urlParams.get('area');
    }

    if (searchInput && urlParams.get('search')) {
        searchInput.value = urlParams.get('search');
    }
});
