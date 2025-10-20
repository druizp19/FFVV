/**
 * ============================================
 * Gestión de Ciclos Comerciales - PharmaSales
 * ============================================
 */

// Obtener CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

/**
 * ============================================
 * Sistema de Notificaciones Toast
 * ============================================
 */

/**
 * Iconos para cada tipo de notificación
 */
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

/**
 * Títulos por defecto para cada tipo
 */
const toastTitles = {
    success: '¡Éxito!',
    error: 'Error',
    warning: 'Advertencia',
    info: 'Información'
};

/**
 * Muestra una notificación toast
 * @param {string} type - Tipo de notificación: 'success', 'error', 'warning', 'info'
 * @param {string} message - Mensaje a mostrar
 * @param {string} title - Título opcional (usa el predeterminado si no se proporciona)
 * @param {number} duration - Duración en ms (por defecto 5000)
 */
window.showToast = function(type, message, title = null, duration = 5000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Crear el elemento toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.style.position = 'relative';

    const toastId = 'toast_' + Date.now();
    toast.id = toastId;

    // Construir el HTML del toast
    toast.innerHTML = `
        <div class="toast-icon">
            ${toastIcons[type]}
        </div>
        <div class="toast-content">
            <div class="toast-title">${title || toastTitles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast('${toastId}')" aria-label="Cerrar">
            ×
        </button>
        <div class="toast-progress"></div>
    `;

    // Agregar al contenedor
    container.appendChild(toast);

    // Auto-remover después de la duración especificada
    setTimeout(() => {
        closeToast(toastId);
    }, duration);
}

/**
 * Cierra un toast específico
 * @param {string} toastId - ID del toast a cerrar
 */
window.closeToast = function(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('removing');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

/**
 * Abre el date picker para un campo de fecha
 * @param {string} inputId - ID del input de fecha
 */
window.openDatePicker = function(inputId) {
    const realInput = document.getElementById(inputId + '_real');
    if (realInput) {
        realInput.showPicker();
    }
}

/**
 * Actualiza el display de la fecha en formato legible
 * @param {string} inputId - ID del input visible
 * @param {string} value - Valor de la fecha en formato YYYY-MM-DD
 */
window.updateDateDisplay = function(inputId, value) {
    const displayInput = document.getElementById(inputId);
    if (displayInput && value) {
        // Convertir de YYYY-MM-DD a DD/MM/YYYY
        const date = new Date(value + 'T00:00:00');
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        displayInput.value = `${day}/${month}/${year}`;
    }
}

/**
 * Abre el modal para crear un nuevo ciclo
 */
window.openModal = function() {
    document.getElementById('cicloModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Nuevo Ciclo';
    document.getElementById('cicloForm').reset();
    document.getElementById('cicloId').value = '';
    // Limpiar los campos de fecha
    document.getElementById('fechaInicio').value = '';
    document.getElementById('fechaFin').value = '';
    document.getElementById('fechaInicio_real').value = '';
    document.getElementById('fechaFin_real').value = '';
    // Mostrar el checkbox de copiar datos
    document.getElementById('copiarDatosContainer').style.display = 'block';
    document.getElementById('copiarDatosCheck').checked = true;
    document.getElementById('copiarDatos').value = 'true';
}

/**
 * Cierra el modal
 */
window.closeModal = function() {
    document.getElementById('cicloModal').classList.remove('active');
}

/**
 * Guarda un ciclo (crear o actualizar)
 * @param {Event} event - Evento del formulario
 */
window.guardarCiclo = async function(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const cicloId = document.getElementById('cicloId').value;
    const copiarDatos = document.getElementById('copiarDatos').value === 'true';
    
    // Obtener las fechas de los inputs reales (ocultos)
    const fechaInicio = document.getElementById('fechaInicio_real').value;
    const fechaFin = document.getElementById('fechaFin_real').value;

    const data = {
        ciclo: formData.get('Ciclo'),
        fechaInicio: fechaInicio,
        fechaFin: fechaFin
    };

    // Si estamos editando, usar la ruta de actualización normal
    if (cicloId) {
        const url = `/ciclos/${cicloId}`;
        const method = 'PUT';

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
                showToast('success', result.message, '¡Ciclo actualizado!');
                window.closeModal();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('error', result.message, 'Error al actualizar');
            }
        } catch (error) {
            showToast('error', 'No se pudo conectar con el servidor. Intenta nuevamente.', 'Error de conexión');
            console.error('Error:', error);
        }
        return;
    }

    // Si estamos creando un nuevo ciclo
    if (copiarDatos) {
        // Copiar datos del último ciclo
        await copiarUltimoCiclo(data);
    } else {
        // Crear ciclo vacío
        await crearCicloVacio(data);
    }
}

/**
 * Crea un ciclo vacío sin copiar datos
 */
async function crearCicloVacio(data) {
    try {
        const response = await fetch('/ciclos', {
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
            showToast('success', 'Ciclo creado exitosamente (sin datos).', '¡Ciclo creado!');
            window.closeModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('error', result.message, 'Error al crear');
        }
    } catch (error) {
        showToast('error', 'No se pudo conectar con el servidor.', 'Error de conexión');
        console.error('Error:', error);
    }
}

/**
 * Copia el último ciclo con todos sus datos relacionados
 */
async function copiarUltimoCiclo(data) {
    try {
        // Primero, obtener el último ciclo
        showToast('info', 'Obteniendo el último ciclo...', 'Procesando');
        
        const obtenerResponse = await fetch('/ciclos/ultimo', {
            headers: {
                'Accept': 'application/json'
            }
        });

        const obtenerResult = await obtenerResponse.json();

        if (!obtenerResult.success || !obtenerResult.ciclo) {
            showToast('warning', 'No hay ciclos anteriores. Se creará un ciclo vacío.', 'Sin ciclos anteriores');
            await crearCicloVacio(data);
            return;
        }

        const ultimoCicloId = obtenerResult.ciclo.idCiclo;
        
        showToast('info', 'Copiando datos del ciclo anterior...', 'Clonando');

        // Copiar el ciclo completo
        const copiarResponse = await fetch(`/ciclos/${ultimoCicloId}/copiar-completo`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const copiarResult = await copiarResponse.json();

        if (copiarResult.success) {
            const stats = copiarResult.data.estadisticas;
            const mensaje = `Ciclo clonado exitosamente. Se copiaron: ${stats.productos} productos, ${stats.zonas_empleados} zonas-empleados, ${stats.zonas_geosegmentos} zonas-geosegmentos y ${stats.fuerzas_venta} registros de fuerza de venta.`;
            showToast('success', mensaje, '¡Ciclo clonado!', 8000);
            window.closeModal();
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showToast('error', copiarResult.message, 'Error al clonar');
        }
    } catch (error) {
        showToast('error', 'No se pudo conectar con el servidor.', 'Error de conexión');
        console.error('Error:', error);
    }
}

/**
 * Carga los datos de un ciclo en el modal para editar
 * @param {number} id - ID del ciclo a editar
 */
window.editarCiclo = async function(id) {
    try {
        const response = await fetch(`/ciclos/${id}`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.success) {
            const ciclo = result.ciclo;
            document.getElementById('modalTitle').textContent = 'Editar Ciclo';
            document.getElementById('cicloId').value = ciclo.idCiclo;
            document.getElementById('nombreCiclo').value = ciclo.ciclo || '';
            
            // Actualizar inputs de fecha reales y visibles
            document.getElementById('fechaInicio_real').value = ciclo.fechaInicio;
            document.getElementById('fechaFin_real').value = ciclo.fechaFin;
            updateDateDisplay('fechaInicio', ciclo.fechaInicio);
            updateDateDisplay('fechaFin', ciclo.fechaFin);
            
            // Ocultar el checkbox de copiar datos al editar
            document.getElementById('copiarDatosContainer').style.display = 'none';
            
            document.getElementById('cicloModal').classList.add('active');
        } else {
            showToast('error', 'No se pudo cargar el ciclo.', 'Error');
        }
    } catch (error) {
        showToast('error', 'No se pudo conectar con el servidor.', 'Error de conexión');
        console.error('Error:', error);
    }
}

/**
 * Copia la configuración de un ciclo con nuevas fechas
 * @param {number} id - ID del ciclo a copiar
 */
window.copiarCiclo = async function(id) {
    // Obtener el ciclo original
    try {
        const response = await fetch(`/ciclos/${id}`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (result.success) {
            const ciclo = result.ciclo;
            // Abrir el modal con los datos del ciclo a copiar
            document.getElementById('modalTitle').textContent = 'Copiar Ciclo';
            document.getElementById('cicloId').value = ''; // Nuevo ciclo
            document.getElementById('nombreCiclo').value = ciclo.ciclo + ' (Copia)';
            
            // Limpiar los campos de fecha
            document.getElementById('fechaInicio').value = '';
            document.getElementById('fechaFin').value = '';
            document.getElementById('fechaInicio_real').value = '';
            document.getElementById('fechaFin_real').value = '';
            
            document.getElementById('cicloModal').classList.add('active');
            showToast('info', 'Asigna las fechas para el nuevo ciclo.', 'Copiar Ciclo');
        } else {
            showToast('error', 'No se pudo cargar el ciclo para copiar.', 'Error');
        }
    } catch (error) {
        showToast('error', 'No se pudo conectar con el servidor.', 'Error de conexión');
        console.error('Error:', error);
    }
}

/**
 * Inicialización cuando el DOM está listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal al hacer clic fuera
    const modal = document.getElementById('cicloModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeModal();
            }
        });
    }
});

