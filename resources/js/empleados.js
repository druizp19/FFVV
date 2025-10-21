/* ============================================
   EMPLEADOS MODULE - JavaScript
   ============================================ */

// Estado global
let employeeToDeactivate = null;

/* ====================
   MODAL MANAGEMENT
   ==================== */

/**
 * Abre el modal para crear o editar empleado
 */
function openModal(mode, employeeId = null) {
    const modal = document.getElementById('employeeModal');
    const title = document.getElementById('modalTitle');
    const form = document.getElementById('employeeForm');
    const estadoGroup = document.getElementById('estadoGroup');
    const idEstadoSelect = document.getElementById('idEstado');
    
    form.reset();
    document.getElementById('employeeId').value = '';
    
    if (mode === 'create') {
        title.textContent = 'Nuevo Empleado';
        // Ocultar selector de estado y establecer "Activo" por defecto
        estadoGroup.style.display = 'none';
        idEstadoSelect.removeAttribute('required');
        // Encontrar y seleccionar "Activo" (estado 1)
        const activeOption = Array.from(idEstadoSelect.options).find(opt => opt.value == '1');
        if (activeOption) {
            idEstadoSelect.value = activeOption.value;
        }
    } else if (mode === 'edit' && employeeId) {
        title.textContent = 'Editar Empleado';
        // Mostrar selector de estado en modo edición
        estadoGroup.style.display = 'block';
        idEstadoSelect.setAttribute('required', 'required');
        loadEmployeeData(employeeId);
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/**
 * Cierra el modal de empleado
 */
function closeModal() {
    const modal = document.getElementById('employeeModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

/**
 * Abre el modal de confirmación
 */
function confirmDeactivate(employeeId, employeeName) {
    employeeToDeactivate = employeeId;
    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    
    message.textContent = `¿Estás seguro de que deseas desactivar a ${employeeName}?`;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/**
 * Cierra el modal de confirmación
 */
function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    employeeToDeactivate = null;
}

// Cerrar modales con tecla ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
        closeConfirmModal();
    }
});

/* ====================
   CRUD OPERATIONS
   ==================== */

/**
 * Carga los datos de un empleado para editar
 */
async function loadEmployeeData(employeeId) {
    try {
        const response = await fetch(`/empleados/${employeeId}`);
        
        if (!response.ok) {
            throw new Error('Error al cargar los datos del empleado');
        }
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Error al cargar el empleado');
        }
        
        const employee = result.data;
        
        // Llenar el formulario
        document.getElementById('employeeId').value = employee.idEmpleado || '';
        document.getElementById('dni').value = employee.dni || '';
        document.getElementById('nombre').value = employee.nombre || '';
        document.getElementById('apeNombre').value = employee.apeNombre || '';
        document.getElementById('correo').value = employee.correo || '';
        document.getElementById('idCargo').value = employee.idCargo || '';
        document.getElementById('idArea').value = employee.idArea || '';
        document.getElementById('idUNE').value = employee.idUne || '';
        document.getElementById('idEstado').value = employee.idEstado || '';
        
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message || 'Error al cargar el empleado', 'error');
    }
}

/**
 * Edita un empleado
 */
function editEmployee(employeeId) {
    openModal('edit', employeeId);
}

/**
 * Guarda un empleado (crear o editar)
 */
async function saveEmployee(event) {
    event.preventDefault();
    
    const employeeId = document.getElementById('employeeId').value;
    const isEditing = employeeId !== '';
    
    const data = {
        dni: document.getElementById('dni').value.trim(),
        nombre: document.getElementById('nombre').value.trim(),
        apeNombre: document.getElementById('apeNombre').value.trim(),
        correo: document.getElementById('correo').value.trim() || null,
        idCargo: parseInt(document.getElementById('idCargo').value),
        idArea: parseInt(document.getElementById('idArea').value),
        idUNE: parseInt(document.getElementById('idUNE').value),
        idEstado: parseInt(document.getElementById('idEstado').value),
    };
    
    try {
        const url = isEditing ? `/empleados/${employeeId}` : '/empleados';
        const method = isEditing ? 'PUT' : 'POST';
        
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
                isEditing ? 'Empleado actualizado exitosamente' : 'Empleado creado exitosamente',
                'success'
            );
            closeModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(result.message || 'Error al guardar el empleado', 'error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al procesar la solicitud', 'error');
    }
}

/**
 * Desactiva un empleado
 */
async function deactivateEmployee() {
    if (!employeeToDeactivate) return;
    
    try {
        const response = await fetch(`/empleados/${employeeToDeactivate}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Empleado desactivado exitosamente', 'success');
            closeConfirmModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(result.message || 'Error al desactivar el empleado', 'error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al procesar la solicitud', 'error');
    }
}

/* ====================
   SEARCH & FILTERS
   ==================== */

let searchTimeout;

/**
 * Búsqueda de empleados con debounce
 */
function searchEmployees() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
}

/**
 * Aplica todos los filtros
 */
function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const filterCargo = document.getElementById('filterCargo').value;
    const filterArea = document.getElementById('filterArea').value;
    const filterUNE = document.getElementById('filterUNE').value;
    
    const rows = document.querySelectorAll('#employeesTable tr');
    
    rows.forEach(row => {
        const nombre = row.querySelector('.employee-name')?.textContent.toLowerCase() || '';
        const apellido = row.querySelector('.employee-subtitle')?.textContent.toLowerCase() || '';
        const dni = row.querySelector('.badge-light')?.textContent.toLowerCase() || '';
        const cargo = row.children[2]?.textContent || '';
        const area = row.children[3]?.textContent || '';
        const une = row.children[4]?.textContent || '';
        
        const matchSearch = nombre.includes(search) || 
                          apellido.includes(search) || 
                          dni.includes(search) ||
                          cargo.toLowerCase().includes(search);
        
        const matchCargo = !filterCargo || cargo === document.querySelector(`#filterCargo option[value="${filterCargo}"]`)?.textContent;
        const matchArea = !filterArea || area === document.querySelector(`#filterArea option[value="${filterArea}"]`)?.textContent;
        const matchUNE = !filterUNE || une === document.querySelector(`#filterUNE option[value="${filterUNE}"]`)?.textContent;
        
        if (matchSearch && matchCargo && matchArea && matchUNE) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Limpia todos los filtros
 */
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterCargo').value = '';
    document.getElementById('filterArea').value = '';
    document.getElementById('filterUNE').value = '';
    applyFilters();
}

/* ====================
   TOAST NOTIFICATIONS
   ==================== */

/**
 * Muestra una notificación toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'warning'
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' 
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>';
    
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

/* ====================
   INITIALIZATION
   ==================== */

// Hacer funciones disponibles globalmente
window.openModal = openModal;
window.closeModal = closeModal;
window.confirmDeactivate = confirmDeactivate;
window.closeConfirmModal = closeConfirmModal;
window.editEmployee = editEmployee;
window.saveEmployee = saveEmployee;
window.deactivateEmployee = deactivateEmployee;
window.searchEmployees = searchEmployees;
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;
window.showToast = showToast;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Empleados cargado');
});

