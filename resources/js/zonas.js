// ============================================
// JavaScript del Módulo de Zonas
// ============================================

let zonaActualId = null;
let geosegmentosAsignados = [];

// ============================================
// Selector de Ciclo
// ============================================

function cambiarCiclo() {
    const cicloId = document.getElementById('cicloSelector').value;
    const url = new URL(window.location.href);
    
    if (cicloId) {
        url.searchParams.set('ciclo', cicloId);
    } else {
        url.searchParams.delete('ciclo');
    }
    
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// ============================================
// Búsqueda con debounce
// ============================================
let searchTimeout;
const searchInput = document.getElementById('searchInput');

if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchValue = e.target.value;
            const url = new URL(window.location.href);
            
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }
            
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 500);
    });
}

// ============================================
// Modal Crear/Editar Zona
// ============================================

function abrirModalCrear() {
    document.getElementById('modalTitle').textContent = 'Nueva Zona';
    document.getElementById('zonaId').value = '';
    document.getElementById('formZona').reset();
    
    // Limpiar asignaciones
    geosegmentosAsignados = [];
    renderGeosegmentosAsignados();
    
    document.getElementById('modalZona').classList.add('active');
}

function abrirModalEditar(id) {
    document.getElementById('modalTitle').textContent = 'Editar Zona';
    document.getElementById('zonaId').value = id;
    
    // Obtener datos de la zona
    fetch(`/zonas/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('zona').value = data.data.zona;
                document.getElementById('idEstado').value = data.data.idEstado;
                document.getElementById('modalZona').classList.add('active');
            } else {
                mostrarToast(data.message || 'Error al cargar la zona', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error al cargar la zona', 'error');
        });
}

function cerrarModal() {
    document.getElementById('modalZona').classList.remove('active');
    document.getElementById('formZona').reset();
    geosegmentosAsignados = [];
}

function guardarZona(event) {
    event.preventDefault();
    
    const id = document.getElementById('zonaId').value;
    const formData = {
        zona: document.getElementById('zona').value,
        idEstado: 1, // Siempre activo por defecto
        geosegmentos: geosegmentosAsignados.map(g => ({
            idGeosegmento: g.idGeosegmento,
            idCiclo: g.idCiclo,
            nuevoGeosegmento: g.nuevoGeosegmento || null,
            nuevoLugar: g.nuevoLugar || null
        }))
    };
    
    const url = id ? `/zonas/${id}` : '/zonas';
    const method = id ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast(data.message || 'Zona guardada exitosamente', 'success');
            cerrarModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(data.message || 'Error al guardar la zona', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error al guardar la zona', 'error');
    });
}

// ============================================
// Desactivar Zona
// ============================================

let zonaIdDesactivar = null;

function confirmarDesactivar(id, nombre) {
    zonaIdDesactivar = id;
    document.getElementById('confirmMessage').textContent = `¿Estás seguro de que deseas desactivar la zona "${nombre}"?`;
    document.getElementById('confirmModal').classList.add('active');
}

function cerrarModalConfirmar() {
    document.getElementById('confirmModal').classList.remove('active');
    zonaIdDesactivar = null;
}

function desactivarZona() {
    if (!zonaIdDesactivar) return;
    
    fetch(`/zonas/${zonaIdDesactivar}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        cerrarModalConfirmar();
        if (data.success) {
            mostrarToast(data.message || 'Zona desactivada exitosamente', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(data.message || 'Error al desactivar la zona', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        cerrarModalConfirmar();
        mostrarToast('Error al desactivar la zona', 'error');
    });
}

// ============================================
// Modal Ver Detalle
// ============================================

function verDetalle(id) {
    zonaActualId = id;
    
    fetch(`/zonas/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const zona = data.data;
                
                // Llenar información de la zona
                document.getElementById('detalleZonaNombre').textContent = zona.zona;
                const estadoBadge = document.getElementById('detalleZonaEstado');
                estadoBadge.textContent = zona.estado?.estado || 'N/A';
                estadoBadge.className = 'badge ' + (zona.estado?.estado === 'Activo' ? 'badge-success' : 'badge-danger');
                
                // Obtener ciclo seleccionado
                const cicloId = getCicloSeleccionado();
                
                // Cargar datos de los tabs
                cargarEmpleadosAsignados(id, cicloId);
                cargarGeosegmentosAsignados(id, cicloId);
                cargarUbigeos(id, cicloId);
                
                // Mostrar modal
                document.getElementById('modalDetalle').classList.add('active');
                
                // Activar primer tab
                cambiarTab('empleados');
            } else {
                mostrarToast(data.message || 'Error al cargar el detalle', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error al cargar el detalle', 'error');
        });
}

function getCicloSeleccionado() {
    const cicloSelector = document.getElementById('cicloSelector');
    return cicloSelector ? cicloSelector.value : null;
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalle').classList.remove('active');
    zonaActualId = null;
}

// ============================================
// Tabs
// ============================================

function cambiarTab(tabName) {
    // Remover active de todos los botones y contenidos
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Activar el tab seleccionado
    document.querySelector(`.tab-btn[onclick="cambiarTab('${tabName}')"]`)?.classList.add('active');
    document.getElementById(`tab${capitalize(tabName)}`).classList.add('active');
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// ============================================
// Cargar Empleados Asignados con Paginación
// ============================================

let empleadosData = [];
let empleadosCurrentPage = 1;
const empleadosPerPage = 5;

function cargarEmpleadosAsignados(zonaId, cicloId = null) {
    const container = document.getElementById('tablaEmpleados');
    container.innerHTML = '<div class="tab-empty">Cargando empleados...</div>';
    
    const url = cicloId ? `/zonas/${zonaId}/empleados?ciclo=${cicloId}` : `/zonas/${zonaId}/empleados`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                empleadosData = data.data;
                empleadosCurrentPage = 1;
                renderEmpleados();
            } else {
                container.innerHTML = '<div class="tab-empty">No hay empleados asignados a esta zona</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="tab-empty">Error al cargar los empleados</div>';
        });
}

function renderEmpleados() {
    const container = document.getElementById('tablaEmpleados');
    const totalPages = Math.ceil(empleadosData.length / empleadosPerPage);
    const startIndex = (empleadosCurrentPage - 1) * empleadosPerPage;
    const endIndex = startIndex + empleadosPerPage;
    const currentEmpleados = empleadosData.slice(startIndex, endIndex);
    
    let html = `
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Cargo</th>
                    <th>Ciclo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    currentEmpleados.forEach(asignacion => {
        const empleado = asignacion.empleado || {};
        const cargo = empleado.cargo || {};
        const ciclo = asignacion.ciclo || {};
        const estado = asignacion.estado || {};
        
        html += `
            <tr>
                <td>${empleado.apeNombre || 'N/A'}</td>
                <td>${cargo.cargo || 'N/A'}</td>
                <td>${ciclo.ciclo || 'N/A'}</td>
                <td>
                    <span class="badge ${estado.estado === 'Activo' ? 'badge-success' : 'badge-danger'}">
                        ${estado.estado || 'N/A'}
                    </span>
                </td>
                <td>
                    <button class="btn-icon-action btn-delete" onclick="desasignarEmpleado(${asignacion.idZonaEmp})" title="Desasignar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    
    // Agregar paginación
    if (totalPages > 1) {
        html += `
            <div class="ubigeos-pagination">
                <div class="pagination-info-small">
                    Mostrando ${startIndex + 1}-${Math.min(endIndex, empleadosData.length)} de ${empleadosData.length} empleados
                </div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" ${empleadosCurrentPage === 1 ? 'disabled' : ''} onclick="cambiarPaginaEmpleados(${empleadosCurrentPage - 1})">
                        ‹
                    </button>
                    <span class="pagination-current">Página ${empleadosCurrentPage} de ${totalPages}</span>
                    <button class="pagination-btn" ${empleadosCurrentPage === totalPages ? 'disabled' : ''} onclick="cambiarPaginaEmpleados(${empleadosCurrentPage + 1})">
                        ›
                    </button>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

function cambiarPaginaEmpleados(page) {
    const totalPages = Math.ceil(empleadosData.length / empleadosPerPage);
    if (page >= 1 && page <= totalPages) {
        empleadosCurrentPage = page;
        renderEmpleados();
    }
}

// ============================================
// Cargar Geosegmentos Asignados con Paginación
// ============================================

let geosegmentosData = [];
let geosegmentosCurrentPage = 1;
const geosegmentosPerPage = 5;

function cargarGeosegmentosAsignados(zonaId, cicloId = null) {
    const container = document.getElementById('tablaGeosegmentos');
    container.innerHTML = '<div class="tab-empty">Cargando geosegmentos...</div>';
    
    const url = cicloId ? `/zonas/${zonaId}/geosegmentos?ciclo=${cicloId}` : `/zonas/${zonaId}/geosegmentos`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                geosegmentosData = data.data;
                geosegmentosCurrentPage = 1;
                renderGeosegmentos();
            } else {
                container.innerHTML = '<div class="tab-empty">No hay geosegmentos asignados a esta zona</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="tab-empty">Error al cargar los geosegmentos</div>';
        });
}

function renderGeosegmentos() {
    const container = document.getElementById('tablaGeosegmentos');
    const totalPages = Math.ceil(geosegmentosData.length / geosegmentosPerPage);
    const startIndex = (geosegmentosCurrentPage - 1) * geosegmentosPerPage;
    const endIndex = startIndex + geosegmentosPerPage;
    const currentGeosegmentos = geosegmentosData.slice(startIndex, endIndex);
    
    let html = `
        <table>
            <thead>
                <tr>
                    <th>Geosegmento</th>
                    <th>Lugar</th>
                    <th>Ciclo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    currentGeosegmentos.forEach(asignacion => {
        const geosegmento = asignacion.geosegmento || {};
        const ciclo = asignacion.ciclo || {};
        const estado = asignacion.estado || {};
        
        html += `
            <tr>
                <td>${geosegmento.geosegmento || 'N/A'}</td>
                <td>${geosegmento.lugar || 'N/A'}</td>
                <td>${ciclo.ciclo || 'N/A'}</td>
                <td>
                    <span class="badge ${estado.estado === 'Activo' ? 'badge-success' : 'badge-danger'}">
                        ${estado.estado || 'N/A'}
                    </span>
                </td>
                <td>
                    <button class="btn-icon-action btn-delete" onclick="desasignarGeosegmento(${asignacion.idZonaGeo})" title="Desasignar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    
    // Agregar paginación
    if (totalPages > 1) {
        html += `
            <div class="ubigeos-pagination">
                <div class="pagination-info-small">
                    Mostrando ${startIndex + 1}-${Math.min(endIndex, geosegmentosData.length)} de ${geosegmentosData.length} geosegmentos
                </div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" ${geosegmentosCurrentPage === 1 ? 'disabled' : ''} onclick="cambiarPaginaGeosegmentos(${geosegmentosCurrentPage - 1})">
                        ‹
                    </button>
                    <span class="pagination-current">Página ${geosegmentosCurrentPage} de ${totalPages}</span>
                    <button class="pagination-btn" ${geosegmentosCurrentPage === totalPages ? 'disabled' : ''} onclick="cambiarPaginaGeosegmentos(${geosegmentosCurrentPage + 1})">
                        ›
                    </button>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

function cambiarPaginaGeosegmentos(page) {
    const totalPages = Math.ceil(geosegmentosData.length / geosegmentosPerPage);
    if (page >= 1 && page <= totalPages) {
        geosegmentosCurrentPage = page;
        renderGeosegmentos();
    }
}

// ============================================
// Cargar Ubigeos con Paginación
// ============================================

let ubigeosData = [];
let ubigeosCurrentPage = 1;
const ubigeosPerPage = 5;

function cargarUbigeos(zonaId, cicloId = null) {
    const container = document.getElementById('tablaUbigeos');
    container.innerHTML = '<div class="tab-empty">Cargando ubigeos...</div>';
    
    const url = cicloId ? `/zonas/${zonaId}/ubigeos?ciclo=${cicloId}` : `/zonas/${zonaId}/ubigeos`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                ubigeosData = data.data;
                ubigeosCurrentPage = 1;
                renderUbigeos();
            } else {
                container.innerHTML = '<div class="tab-empty">No hay ubigeos relacionados con esta zona</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="tab-empty">Error al cargar los ubigeos</div>';
        });
}

function renderUbigeos() {
    const container = document.getElementById('tablaUbigeos');
    const totalPages = Math.ceil(ubigeosData.length / ubigeosPerPage);
    const startIndex = (ubigeosCurrentPage - 1) * ubigeosPerPage;
    const endIndex = startIndex + ubigeosPerPage;
    const currentUbigeos = ubigeosData.slice(startIndex, endIndex);
    
    let html = `
        <table>
            <thead>
                <tr>
                    <th>Ubigeo</th>
                    <th>Departamento</th>
                    <th>Provincia</th>
                    <th>Distrito</th>
                    <th>Geosegmento</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    currentUbigeos.forEach(ubigeo => {
        const geosegmento = ubigeo.geosegmento || {};
        
        html += `
            <tr>
                <td>${ubigeo.ubigeo || 'N/A'}</td>
                <td>${ubigeo.departamento || 'N/A'}</td>
                <td>${ubigeo.provincia || 'N/A'}</td>
                <td>${ubigeo.distrito || 'N/A'}</td>
                <td>${geosegmento.geosegmento || 'N/A'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    
    // Agregar paginación
    if (totalPages > 1) {
        html += `
            <div class="ubigeos-pagination">
                <div class="pagination-info-small">
                    Mostrando ${startIndex + 1}-${Math.min(endIndex, ubigeosData.length)} de ${ubigeosData.length} ubigeos
                </div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" ${ubigeosCurrentPage === 1 ? 'disabled' : ''} onclick="cambiarPaginaUbigeos(${ubigeosCurrentPage - 1})">
                        ‹
                    </button>
                    <span class="pagination-current">Página ${ubigeosCurrentPage} de ${totalPages}</span>
                    <button class="pagination-btn" ${ubigeosCurrentPage === totalPages ? 'disabled' : ''} onclick="cambiarPaginaUbigeos(${ubigeosCurrentPage + 1})">
                        ›
                    </button>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

function cambiarPaginaUbigeos(page) {
    const totalPages = Math.ceil(ubigeosData.length / ubigeosPerPage);
    if (page >= 1 && page <= totalPages) {
        ubigeosCurrentPage = page;
        renderUbigeos();
    }
}

// Búsqueda de ubigeos en el tab
const searchUbigeo = document.getElementById('searchUbigeo');
if (searchUbigeo) {
    let ubigeoSearchTimeout;
    searchUbigeo.addEventListener('input', function(e) {
        clearTimeout(ubigeoSearchTimeout);
        ubigeoSearchTimeout = setTimeout(() => {
            if (zonaActualId) {
                cargarUbigeos(zonaActualId, e.target.value);
            }
        }, 300);
    });
}

// ============================================
// Toggle entre crear nuevo y seleccionar existente
// ============================================

function toggleGeosegmentoMode() {
    const tipoSeleccionado = document.querySelector('input[name="tipoGeosegmento"]:checked').value;
    const existenteDiv = document.getElementById('geosegmentoExistente');
    const nuevoDiv = document.getElementById('geosegmentoNuevo');
    const geosegmentoSelect = document.getElementById('geosegmentoSelect');
    const nuevoGeosegmento = document.getElementById('nuevoGeosegmento');
    const nuevoLugar = document.getElementById('nuevoLugar');
    
    if (tipoSeleccionado === 'existente') {
        existenteDiv.style.display = 'block';
        nuevoDiv.style.display = 'none';
        geosegmentoSelect.required = true;
        nuevoGeosegmento.required = false;
        nuevoLugar.required = false;
    } else {
        existenteDiv.style.display = 'none';
        nuevoDiv.style.display = 'block';
        geosegmentoSelect.required = false;
        nuevoGeosegmento.required = true;
        nuevoLugar.required = true;
    }
}

// ============================================
// Modales de Asignación
// ============================================

function abrirModalAsignarGeosegmento() {
    document.getElementById('formAsignarGeosegmento').reset();
    // Reset al modo existente
    document.querySelector('input[name="tipoGeosegmento"][value="existente"]').checked = true;
    toggleGeosegmentoMode();
    document.getElementById('modalAsignarGeosegmento').classList.add('active');
}

function cerrarModalAsignarGeosegmento() {
    document.getElementById('modalAsignarGeosegmento').classList.remove('active');
}

function agregarGeosegmento(event) {
    event.preventDefault();
    
    const tipoSeleccionado = document.querySelector('input[name="tipoGeosegmento"]:checked').value;
    const cicloSelect = document.getElementById('cicloGeosegmento');
    const cicloId = cicloSelect.value;
    const cicloNombre = cicloSelect.options[cicloSelect.selectedIndex].text;
    
    if (tipoSeleccionado === 'existente') {
        // Seleccionar geosegmento existente
        const geosegmentoSelect = document.getElementById('geosegmentoSelect');
        const geosegmentoId = geosegmentoSelect.value;
        const geosegmentoNombre = geosegmentoSelect.options[geosegmentoSelect.selectedIndex].text;
        
        if (!geosegmentoId) {
            mostrarToast('Debe seleccionar un geosegmento', 'warning');
            return;
        }
        
        // Verificar si ya está asignado
        const yaAsignado = geosegmentosAsignados.some(g => g.idGeosegmento === parseInt(geosegmentoId) && g.idCiclo === parseInt(cicloId));
        if (yaAsignado) {
            mostrarToast('Este geosegmento ya está asignado a este ciclo', 'warning');
            return;
        }
        
        geosegmentosAsignados.push({
            idGeosegmento: parseInt(geosegmentoId),
            nombreGeosegmento: geosegmentoNombre,
            idCiclo: parseInt(cicloId),
            nombreCiclo: cicloNombre
        });
    } else {
        // Crear nuevo geosegmento
        const nuevoGeosegmento = document.getElementById('nuevoGeosegmento').value;
        const nuevoLugar = document.getElementById('nuevoLugar').value;
        
        if (!nuevoGeosegmento || !nuevoLugar) {
            mostrarToast('Debe completar todos los campos del nuevo geosegmento', 'warning');
            return;
        }
        
        geosegmentosAsignados.push({
            idGeosegmento: null, // Será creado
            nombreGeosegmento: nuevoGeosegmento,
            nuevoGeosegmento: nuevoGeosegmento,
            nuevoLugar: nuevoLugar,
            idCiclo: parseInt(cicloId),
            nombreCiclo: cicloNombre
        });
    }
    
    renderGeosegmentosAsignados();
    cerrarModalAsignarGeosegmento();
    mostrarToast('Geosegmento agregado', 'success');
}

// ============================================
// Renderizar Listas de Asignaciones
// ============================================

function renderGeosegmentosAsignados() {
    const container = document.getElementById('listaGeosegmentosAsignados');
    
    if (geosegmentosAsignados.length === 0) {
        container.innerHTML = '<p class="empty-message">No hay geosegmentos asignados</p>';
        return;
    }
    
    let html = '';
    geosegmentosAsignados.forEach((geosegmento, index) => {
        const etiqueta = geosegmento.nuevoGeosegmento ? ' (Nuevo)' : '';
        html += `
            <div class="asignacion-item">
                <div class="asignacion-info">
                    <div class="asignacion-nombre">${geosegmento.nombreGeosegmento}${etiqueta}</div>
                    <div class="asignacion-detalle">Ciclo: ${geosegmento.nombreCiclo}</div>
                </div>
                <div class="asignacion-actions">
                    <button type="button" class="btn-remove" onclick="removerGeosegmento(${index})" title="Eliminar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function removerGeosegmento(index) {
    geosegmentosAsignados.splice(index, 1);
    renderGeosegmentosAsignados();
    mostrarToast('Geosegmento removido', 'success');
}

// ============================================
// Asignar/Desasignar desde el detalle (placeholder)
// ============================================

function abrirModalAsignarGeosegmentoDetalle() {
    mostrarToast('Funcionalidad en desarrollo', 'warning');
}

function desasignarEmpleado(id) {
    if (confirm('¿Estás seguro de que deseas desasignar este empleado?')) {
        mostrarToast('Funcionalidad en desarrollo', 'warning');
    }
}

function desasignarGeosegmento(id) {
    if (confirm('¿Estás seguro de que deseas desasignar este geosegmento?')) {
        mostrarToast('Funcionalidad en desarrollo', 'warning');
    }
}

// ============================================
// Toast de notificaciones
// ============================================

function mostrarToast(mensaje, tipo = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = mensaje;
    toast.className = `toast ${tipo} show`;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ============================================
// Cerrar modales al hacer clic fuera
// ============================================

document.getElementById('modalZona')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});

document.getElementById('modalDetalle')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalDetalle();
    }
});

document.getElementById('modalAsignarGeosegmento')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalAsignarGeosegmento();
    }
});

document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalConfirmar();
    }
});

