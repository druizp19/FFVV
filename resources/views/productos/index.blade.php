@extends('layouts.app')

@section('title', 'Productos - FFVV')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-text">
            <h1 class="page-title">Gestión de Productos</h1>
            <p class="page-subtitle">Consulta y filtra productos por ciclo y estado</p>
        </div>
    </div>
</div>

{{-- Filters Section --}}
<div class="filters-section">
    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Ciclo</span>
        </div>
        <select class="filter-select" id="cycleFilter" onchange="filterProducts()">
            <option value="">Todos los ciclos</option>
            @foreach($ciclos as $c)
                @php
                    $esCerrado = false;
                    if ($c->fechaFin) {
                        $fechaFin = \Carbon\Carbon::parse($c->fechaFin)->startOfDay();
                        $hoy = \Carbon\Carbon::now()->startOfDay();
                        $esCerrado = $fechaFin->lt($hoy);
                    }
                    if (!$esCerrado && $c->relationLoaded('estado') && $c->getRelation('estado')) {
                        $estadoRelacion = $c->getRelation('estado');
                        if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                            $esCerrado = true;
                        }
                    }
                    $estadoTexto = $esCerrado ? 'Cerrado' : 'Activo';
                @endphp
                <option value="{{ $c->idCiclo }}" {{ request('ciclo') == $c->idCiclo ? 'selected' : '' }}>
                    {{ $c->ciclo ?? $c->idCiclo }} ({{ $estadoTexto }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Marca</span>
        </div>
        <select class="filter-select" id="marcaFilter" onchange="filterProducts()">
            <option value="">Todas las marcas</option>
            @foreach($marcas as $m)
                <option value="{{ $m->idMarca }}" {{ request('marca') == $m->idMarca ? 'selected' : '' }}>
                    {{ $m->marca }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 16v-4"></path>
                <path d="M12 8h.01"></path>
            </svg>
            <span>Estado</span>
        </div>
        <select class="filter-select" id="statusFilter" onchange="filterProducts()">
            <option value="">Todos los estados</option>
            @foreach($estados as $e)
                @php
                    // Si no hay filtro de estado en la URL, seleccionar "Activo" por defecto
                    $isSelected = request()->has('estado') 
                        ? request('estado') == $e->idEstado 
                        : $e->estado === 'Activo';
                @endphp
                <option value="{{ $e->idEstado }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $e->estado }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <span>Buscar</span>
        </div>
        <input type="text" class="filter-input" id="searchInput" placeholder="Buscar producto..." onkeyup="searchProducts()">
    </div>
</div>

{{-- Products Table --}}
<div class="products-table-container">
    <div class="table-wrapper">
        <table class="products-table" id="productsTable">
            <thead>
                <tr>
                    <th>Ciclo</th>
                    <th>Marca</th>
                    <th>Core</th>
                    <th>Cuota</th>
                    <th>Promoción</th>
                    <th>Alcance</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody id="productsTableBody">
                @forelse($productos as $p)
                    <tr data-product-id="{{ $p->idProducto }}" data-marca="{{ $p->marcaMkt->marca->marca ?? 'N/A' }}" data-core="{{ $p->core->core ?? 'N/A' }}">
                        <td>
                            <span class="cycle-badge">{{ $p->ciclo->ciclo ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <div class="product-info">
                                <span class="info-main">{{ $p->marcaMkt->marca->marca ?? 'N/A' }}</span>
                                <span class="info-sub">{{ $p->marcaMkt->mercado->mercado ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="attribute-badge">{{ $p->core->core ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($p->cuota)
                                <span class="attribute-badge">{{ $p->cuota->cuota }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($p->promocion)
                                <span class="attribute-badge badge-promo">{{ $p->promocion->promocion }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($p->alcance)
                                <span class="attribute-badge badge-alcance">{{ $p->alcance->alcance }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ strtolower($p->estado->estado ?? 'inactivo') }}">
                                <span class="status-dot"></span>
                                {{ $p->estado->estado ?? 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="date-group">
                                @if($p->fechaModificacion)
                                    <span class="date-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($p->fechaModificacion)->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="date-item text-muted">Sin fecha</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <button class="action-btn action-edit" onclick="editarProducto({{ $p->idProducto }})" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <path d="M3 9h18"></path>
                                <path d="M9 21V9"></path>
                            </svg>
                            <p>No hay productos registrados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($productos->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando {{ $productos->firstItem() }} - {{ $productos->lastItem() }} de {{ $productos->total() }} productos
            </div>
            <div class="pagination">
                {{ $productos->links('vendor.pagination.custom') }}
            </div>
        </div>
    @endif
</div>

{{-- Modal Editar Producto - Nuevo Diseño --}}
<div class="modal" id="editProductModal">
    <div class="modal-dialog modal-product-new">
        <div class="modal-content-product">
            {{-- Header con gradiente --}}
            <div class="product-modal-header">
                <div class="header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <path d="M3 9h18"></path>
                        <path d="M9 21V9"></path>
                    </svg>
                </div>
                <div class="header-text">
                    <h2>Editar Producto</h2>
                    <p>Actualiza la configuración del producto</p>
                </div>
                <button class="modal-close-new" onclick="closeEditModal()" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="editProductForm" onsubmit="confirmSaveProduct(event)">
                <div class="product-modal-body">
                    <input type="hidden" id="productId">

                    {{-- Tarjeta de información del producto --}}
                    <div class="product-info-card">
                        <div class="info-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="info-card-content">
                            <label class="info-card-label">Producto</label>
                            <input type="text" class="info-card-input" id="productName" disabled>
                        </div>
                    </div>

                    {{-- Sección de configuración --}}
                    <div class="config-section">
                        <div class="section-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M12 1v6m0 6v6"></path>
                                <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                                <path d="M1 12h6m6 0h6"></path>
                                <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                            </svg>
                            <span>Configuración del Producto</span>
                        </div>

                        <div class="config-grid">
                            {{-- Campo Cuota --}}
                            <div class="config-field">
                                <label class="config-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    <span>Cuota</span>
                                </label>
                                <select class="config-select cuota-select" id="idCuota">
                                    <option value="">Seleccionar cuota</option>
                                    @foreach($cuotas as $cuota)
                                        <option value="{{ $cuota->idCuota }}">{{ $cuota->cuota }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Campo Core --}}
                            <div class="config-field">
                                <label class="config-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                        <polyline points="2 17 12 22 22 17"></polyline>
                                        <polyline points="2 12 12 17 22 12"></polyline>
                                    </svg>
                                    <span>Core</span>
                                </label>
                                <select class="config-select core-select" id="idCore">
                                    <option value="">Seleccionar core</option>
                                    @foreach($cores as $core)
                                        <option value="{{ $core->idCore }}">{{ $core->core }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Campo Alcance --}}
                            <div class="config-field">
                                <label class="config-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="12" r="6"></circle>
                                        <circle cx="12" cy="12" r="2"></circle>
                                    </svg>
                                    <span>Alcance</span>
                                </label>
                                <select class="config-select alcance-select" id="idAlcance">
                                    <option value="">Seleccionar alcance</option>
                                    @foreach($alcances as $alcance)
                                        <option value="{{ $alcance->idAlcance }}">{{ $alcance->alcance }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Campo Promoción --}}
                            <div class="config-field">
                                <label class="config-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                    </svg>
                                    <span>Promoción</span>
                                </label>
                                <select class="config-select promocion-select" id="idPromocion">
                                    <option value="">Seleccionar promoción</option>
                                    @foreach($promociones as $promo)
                                        <option value="{{ $promo->idPromocion }}">{{ $promo->promocion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Footer con botones --}}
                    <div class="product-modal-footer">
                        <button type="button" class="btn-cancel-product" onclick="closeEditModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            <span>Cancelar</span>
                        </button>
                        <button type="submit" class="btn-save-product-new">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Confirmación - Diseño Mejorado --}}
<div class="modal" id="confirmProductModal">
    <div class="modal-dialog modal-confirm-product">
        <div class="modal-content-confirm">
            {{-- Icono de confirmación animado --}}
            <div class="confirm-icon-wrapper">
                <div class="confirm-icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>

            {{-- Contenido --}}
            <div class="confirm-content">
                <h2 class="confirm-title">¿Confirmar Cambios?</h2>
                <p class="confirm-description">
                    Los cambios realizados en el producto se guardarán de forma permanente. Esta acción actualizará la configuración del producto.
                </p>
            </div>

            {{-- Botones de acción --}}
            <div class="confirm-actions">
                <button type="button" class="btn-confirm-cancel" onclick="closeConfirmProductModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Cancelar</span>
                </button>
                <button type="button" class="btn-confirm-save" onclick="saveProduct()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Sí, Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toast-container" class="toast-container"></div>

@endsection

@push('styles')
@vite('resources/css/productos.css')
@endpush

@push('scripts')
@vite('resources/js/productos.js')
@endpush
