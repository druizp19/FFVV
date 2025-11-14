/* ============================================
   HISTORIAL MODULE - JavaScript
   ============================================ */

// ==========================================
// 1. STATE MANAGEMENT
// ==========================================
let currentHistorialId = null;

// ==========================================
// 2. MODAL MANAGEMENT
// ==========================================

/**
 * Muestra los detalles de un registro del historial
 */
async function verDetalles(idHistorial) {
    currentHistorialId = idHistorial;
    
    const modal = document.getElementById('detallesModal');
    const detallesContent = document.getElementById('detallesContent');
    
    // Mostrar modal con spinner
    modal.classList.remove('closing');
    modal.classList.add('active');
    
    // Bloquear sidebar en modo responsive
    if (window.innerWidth <= 768 && window.blockSidebar) {
        window.blockSidebar();
    }
    
    detallesContent.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Cargando detalles...</p>
        </div>
    `;
    
    try {
        // Buscar el registro en los datos cargados
        const historial = window.historialData.find(h => h.idHistorial === idHistorial);
        
        if (!historial) {
            throw new Error('Registro no encontrado');
        }
        
        // Renderizar detalles
        detallesContent.innerHTML = renderDetalles(historial);
        
    } catch (error) {
        console.error('Error:', error);
        detallesContent.innerHTML = `
            <div class="error-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                <h3>Error al cargar los detalles</h3>
                <p>${error.message}</p>
            </div>
        `;
    }
}

/**
 * Renderiza los detalles del historial
 */
function renderDetalles(historial) {
    let html = '<div class="detalles-container">';
    
    // Información básica
    html += `
        <div class="detalle-section">
            <h4 class="detalle-title">Información General</h4>
            <div class="detalle-grid">
                <div class="detalle-item">
                    <span class="detalle-label">Entidad:</span>
                    <span class="detalle-value">${historial.entidad}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Acción:</span>
                    <span class="detalle-value">${historial.accion}</span>
                </div>
                ${historial.ciclo ? `
                <div class="detalle-item">
                    <span class="detalle-label">Ciclo:</span>
                    <span class="detalle-value">${historial.ciclo.ciclo}</span>
                </div>
                ` : ''}
                <div class="detalle-item">
                    <span class="detalle-label">Fecha:</span>
                    <span class="detalle-value">${formatFecha(historial.fechaHora)}</span>
                </div>
                ${historial.usuario ? `
                <div class="detalle-item">
                    <span class="detalle-label">Usuario:</span>
                    <span class="detalle-value">${historial.usuario}</span>
                </div>
                ` : ''}
            </div>
        </div>
    `;
    
    // Descripción
    html += `
        <div class="detalle-section">
            <h4 class="detalle-title">Descripción</h4>
            <p class="detalle-description">${historial.descripcion}</p>
        </div>
    `;
    
    // Cambios realizados (comparación lado a lado)
    if (historial.datosAnteriores && historial.datosNuevos && 
        Object.keys(historial.datosAnteriores).length > 0 && 
        Object.keys(historial.datosNuevos).length > 0) {
        
        html += `
            <div class="detalle-section">
                <h4 class="detalle-title">Cambios Realizados</h4>
                <div class="cambios-grid">
        `;
        
        // Obtener todos los campos únicos
        const campos = new Set([
            ...Object.keys(historial.datosAnteriores),
            ...Object.keys(historial.datosNuevos)
        ]);
        
        campos.forEach(campo => {
            const valorAnterior = historial.datosAnteriores[campo] ?? 'Sin asignar';
            const valorNuevo = historial.datosNuevos[campo] ?? 'Sin asignar';
            
            // Solo mostrar si hay cambio
            if (valorAnterior !== valorNuevo) {
                html += `
                    <div class="cambio-item">
                        <div class="cambio-campo">${campo}</div>
                        <div class="cambio-valores">
                            <div class="cambio-anterior">
                                <span class="cambio-label">Anterior:</span>
                                <span class="cambio-value">${valorAnterior}</span>
                            </div>
                            <div class="cambio-arrow">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <div class="cambio-nuevo">
                                <span class="cambio-label">Nuevo:</span>
                                <span class="cambio-value">${valorNuevo}</span>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
        
        html += `
                </div>
            </div>
        `;
    } else if (historial.datosAnteriores && Object.keys(historial.datosAnteriores).length > 0) {
        // Solo datos anteriores (eliminación)
        html += `
            <div class="detalle-section">
                <h4 class="detalle-title">Datos Eliminados</h4>
                <div class="datos-grid">
        `;
        
        Object.entries(historial.datosAnteriores).forEach(([campo, valor]) => {
            html += `
                <div class="dato-item">
                    <span class="dato-label">${campo}:</span>
                    <span class="dato-value">${valor ?? 'Sin asignar'}</span>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    } else if (historial.datosNuevos && Object.keys(historial.datosNuevos).length > 0) {
        // Solo datos nuevos (creación)
        html += `
            <div class="detalle-section">
                <h4 class="detalle-title">Datos Creados</h4>
                <div class="datos-grid">
        `;
        
        Object.entries(historial.datosNuevos).forEach(([campo, valor]) => {
            html += `
                <div class="dato-item">
                    <span class="dato-label">${campo}:</span>
                    <span class="dato-value">${valor ?? 'Sin asignar'}</span>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    
    return html;
}

/**
 * Formatea una fecha
 */
function formatFecha(fecha) {
    const date = new Date(fecha);
    return date.toLocaleString('es-PE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Cierra el modal de detalles
 */
function cerrarDetalles() {
    const modal = document.getElementById('detallesModal');
    modal.classList.add('closing');
    
    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        currentHistorialId = null;
    }, 300);
    
    // Desbloquear sidebar
    if (window.unblockSidebar) {
        window.unblockSidebar();
    }
}

// ==========================================
// 3. TOAST NOTIFICATIONS
// ==========================================

/**
 * Muestra una notificación toast
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' 
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
    
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

// ==========================================
// 4. GLOBAL SCOPE (Para Vite)
// ==========================================

// Exponer funciones al objeto window para que sean accesibles desde HTML
window.verDetalles = verDetalles;
window.cerrarDetalles = cerrarDetalles;
window.showToast = showToast;

// ==========================================
// 5. INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Historial cargado correctamente');
    
    // Cerrar modales con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarDetalles();
        }
    });
    
    // Agregar estilos para los detalles
    const style = document.createElement('style');
    style.textContent = `
        .detalles-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .detalle-section {
            background: var(--bg-secondary);
            border-radius: 0.75rem;
            padding: 1.25rem;
            border: 1px solid var(--border-primary);
        }
        
        .detalle-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 1rem 0;
        }
        
        .detalle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .detalle-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .detalle-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .detalle-value {
            font-size: 0.875rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .detalle-description {
            font-size: 0.875rem;
            color: var(--text-primary);
            line-height: 1.6;
            margin: 0;
        }
        
        .detalle-json {
            background: var(--bg-primary);
            border-radius: 0.5rem;
            padding: 1rem;
            overflow-x: auto;
        }
        
        .detalle-json pre {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
        }
        
        .error-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }
        
        .error-state svg {
            margin: 0 auto 1rem;
            color: var(--danger-500);
        }
        
        .error-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            margin: 0 0 0.5rem 0;
        }
        
        .error-state p {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin: 0;
        }
        
        /* Estilos para cambios lado a lado */
        .cambios-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .cambio-item {
            background: var(--bg-primary);
            border-radius: 0.5rem;
            padding: 1rem;
            border: 1px solid var(--border-primary);
        }
        
        .cambio-campo {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--accent-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        
        .cambio-valores {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 1rem;
            align-items: center;
        }
        
        .cambio-anterior,
        .cambio-nuevo {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .cambio-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .cambio-anterior .cambio-value {
            padding: 0.5rem 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 0.375rem;
            color: #dc2626;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .cambio-nuevo .cambio-value {
            padding: 0.5rem 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 0.375rem;
            color: #059669;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .cambio-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-primary);
        }
        
        /* Estilos para datos simples */
        .datos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 0.75rem;
        }
        
        .dato-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.75rem;
            background: var(--bg-primary);
            border-radius: 0.375rem;
            border: 1px solid var(--border-primary);
        }
        
        .dato-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .dato-value {
            font-size: 0.875rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .cambio-valores {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .cambio-arrow {
                transform: rotate(90deg);
            }
            
            .datos-grid {
                grid-template-columns: 1fr;
            }
        }
    `;
    document.head.appendChild(style);
});
