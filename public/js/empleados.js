/**
 * Empleados Module - PharmaSales
 * Gestión de empleados y sus cargos
 */

// Variables globales
let empleadoIdParaDesactivar = null;

/**
 * Abre el modal para crear un nuevo empleado
 */
function abrirModalCrear() {
    document.getElementById('modalTitle').textContent = 'Nuevo Empleado';
    document.getElementById('empleadoForm').reset();
    document.getElementById('empleadoId').value = '';
    
    // Establecer fecha de ingreso por defecto (hoy)
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fechaIngreso').value = hoy;
    
    document.getElementById('empleadoModal').classList.add('active');
}

/**
 * Abre el modal para editar un empleado existente
 */
async function abrirModalEditar(id) {
    try {
        const response = await fetch(`/empleados/${id}`);
        const result = await response.json();

        if (!result.success) {
            mostrarToast('error', 'Error', result.message);
            return;
        }

        const empleado = result.data;

        document.getElementById('modalTitle').textContent = 'Editar Empleado';
        document.getElementById('empleadoId').value = empleado.idEmpleado;
        document.getElementById('dni').value = empleado.dni;
        document.getElementById('nombre').value = empleado.nombre;
        document.getElementById('apeNombre').value = empleado.apeNombre;
        document.getElementById('correo').value = empleado.correo;
        document.getElementById('celular').value = empleado.celular || '';
        document.getElementById('idCargo').value = empleado.idCargo;
        document.getElementById('idArea').value = empleado.idArea;
        document.getElementById('idUne').value = empleado.idUne;
        document.getElementById('idEstado').value = empleado.idEstado;
        
        // Formatear fechas para el input date
        if (empleado.fechaIngreso) {
            const fechaIngreso = new Date(empleado.fechaIngreso);
            document.getElementById('fechaIngreso').value = fechaIngreso.toISOString().split('T')[0];
        }
        
        if (empleado.fechaCese) {
            const fechaCese = new Date(empleado.fechaCese);
            document.getElementById('fechaCese').value = fechaCese.toISOString().split('T')[0];
        }

        document.getElementById('empleadoModal').classList.add('active');
    } catch (error) {
        console.error('Error al cargar empleado:', error);
        mostrarToast('error', 'Error', 'No se pudo cargar la información del empleado');
    }
}

/**
 * Cierra el modal de empleado
 */
function cerrarModal() {
    document.getElementById('empleadoModal').classList.remove('active');
    document.getElementById('empleadoForm').reset();
}

/**
 * Guarda un empleado (crear o actualizar)
 */
async function guardarEmpleado(event) {
    event.preventDefault();

    const form = event.target;
    const empleadoId = document.getElementById('empleadoId').value;
    const formData = new FormData(form);

    // Convertir FormData a objeto
    const data = {
        idCargo: parseInt(formData.get('idCargo')),
        idArea: parseInt(formData.get('idArea')),
        idUne: parseInt(formData.get('idUne')),
        idEstado: parseInt(formData.get('idEstado')),
        dni: formData.get('dni'),
        nombre: formData.get('nombre'),
        apeNombre: formData.get('apeNombre'),
        correo: formData.get('correo'),
        celular: formData.get('celular') || null,
        fechaIngreso: formData.get('fechaIngreso'),
        fechaCese: formData.get('fechaCese') || null,
    };

    const url = empleadoId ? `/empleados/${empleadoId}` : '/empleados';
    const method = empleadoId ? 'PUT' : 'POST';

    try {
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
            mostrarToast('success', '¡Éxito!', result.message);
            cerrarModal();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            mostrarToast('error', 'Error', result.message);
        }
    } catch (error) {
        console.error('Error al guardar empleado:', error);
        mostrarToast('error', 'Error', 'No se pudo guardar el empleado');
    }
}

/**
 * Abre el modal de confirmación para desactivar un empleado
 */
function confirmarDesactivar(id, nombreCompleto) {
    empleadoIdParaDesactivar = id;
    document.getElementById('confirmMessage').textContent = 
        `¿Estás seguro de que deseas desactivar al empleado "${nombreCompleto}"? Esta acción cambiará su estado a Inactivo.`;
    document.getElementById('confirmModal').classList.add('active');
}

/**
 * Cierra el modal de confirmación
 */
function cerrarModalConfirmar() {
    document.getElementById('confirmModal').classList.remove('active');
    empleadoIdParaDesactivar = null;
}

/**
 * Desactiva un empleado (cambia estado a inactivo)
 */
async function desactivarEmpleado() {
    if (!empleadoIdParaDesactivar) return;

    try {
        const response = await fetch(`/empleados/${empleadoIdParaDesactivar}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast('success', '¡Éxito!', result.message);
            cerrarModalConfirmar();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            mostrarToast('error', 'Error', result.message);
        }
    } catch (error) {
        console.error('Error al desactivar empleado:', error);
        mostrarToast('error', 'Error', 'No se pudo desactivar el empleado');
    }
}

/**
 * Aplica los filtros al formulario
 */
function aplicarFiltros() {
    // Sincronizar el valor de búsqueda
    const searchInput = document.getElementById('searchInput');
    const searchHidden = document.getElementById('searchHidden');
    if (searchInput && searchHidden) {
        searchHidden.value = searchInput.value;
    }
    
    // Enviar el formulario
    document.getElementById('filtersForm').submit();
}

/**
 * Limpia todos los filtros
 */
function limpiarFiltros() {
    // Redirigir a la página sin parámetros
    window.location.href = window.location.pathname;
}

/**
 * Maneja la búsqueda en tiempo real con debounce
 */
let searchTimeout;
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            clearTimeout(searchTimeout);
            
            // Si presiona Enter, aplicar inmediatamente
            if (e.key === 'Enter') {
                aplicarFiltros();
                return;
            }
            
            // Aplicar filtros después de 500ms de inactividad
            searchTimeout = setTimeout(function() {
                aplicarFiltros();
            }, 500);
        });
    }
});

/**
 * Muestra un toast notification
 */
function mostrarToast(tipo, titulo, mensaje) {
    const container = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `toast ${tipo}`;
    
    const iconos = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">${iconos[tipo] || 'ℹ'}</div>
        <div class="toast-content">
            <div class="toast-title">${titulo}</div>
            <div class="toast-message">${mensaje}</div>
        </div>
        <button class="toast-close" onclick="cerrarToast(this)">×</button>
    `;
    
    container.appendChild(toast);
    
    // Auto cerrar después de 5 segundos
    setTimeout(() => {
        cerrarToast(toast.querySelector('.toast-close'));
    }, 5000);
}

/**
 * Cierra un toast notification
 */
function cerrarToast(button) {
    const toast = button.parentElement;
    toast.classList.add('removing');
    
    setTimeout(() => {
        toast.remove();
    }, 300);
}

/**
 * Cerrar modales al hacer clic fuera de ellos
 */
window.addEventListener('click', function(event) {
    const empleadoModal = document.getElementById('empleadoModal');
    const confirmModal = document.getElementById('confirmModal');
    
    if (event.target === empleadoModal) {
        cerrarModal();
    }
    
    if (event.target === confirmModal) {
        cerrarModalConfirmar();
    }
});

/**
 * Cerrar modales con la tecla ESC
 */
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const empleadoModal = document.getElementById('empleadoModal');
        const confirmModal = document.getElementById('confirmModal');
        
        if (empleadoModal.classList.contains('active')) {
            cerrarModal();
        }
        
        if (confirmModal.classList.contains('active')) {
            cerrarModalConfirmar();
        }
    }
});

