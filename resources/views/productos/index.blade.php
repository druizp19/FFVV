@extends('layouts.app')

@section('title', 'Productos - PharmaSales')

@section('content')
<div class="products-container">
    {{-- Header Section --}}
    <header class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">Gestión de Productos</h1>
                <p class="page-description">Consulta y filtra productos por ciclo y estado</p>
            </div>
        </div>
    </header>

    {{-- Filters Section --}}
    <div class="cycle-selector-card">
        <div class="cycle-selector-header">
            <div class="cycle-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3zM9 3v18M3 9h18M3 15h18"/>
                </svg>
            </div>
            <div>
                <h3 class="cycle-title">Filtros de Productos</h3>
                <p class="cycle-description">Filtra por ciclo y estado para visualizar productos específicos</p>
            </div>
        </div>
        <form method="GET" action="{{ route('productos.index') }}" class="filters-form">
            <select class="cycle-select" name="ciclo" id="ciclo">
                <option value="">Todos los ciclos</option>
                @foreach($ciclos as $c)
                    <option value="{{ $c->idCiclo }}" {{ request('ciclo') == $c->idCiclo ? 'selected' : '' }}>
                        {{ $c->ciclo ?? $c->idCiclo }}
                    </option>
                @endforeach
            </select>

            <select class="cycle-select" name="estado" id="estado">
                <option value="">Todos los estados</option>
                @foreach($estados as $e)
                    <option value="{{ $e->idEstado }}" {{ request('estado') == $e->idEstado ? 'selected' : '' }}>
                        {{ $e->estado }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Aplicar
            </button>
            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
                Limpiar
            </a>
        </form>
    </div>

    {{-- Table Container --}}
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ciclo</th>
                    <th>Franquicia-Línea</th>
                    <th>Marca-Mercado</th>
                    <th>Core</th>
                    <th>Cuota</th>
                    <th>Promoción</th>
                    <th>Alcance</th>
                    <th>Estado</th>
                    <th>F. Modificación</th>
                    <th>F. Cierre</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $p)
                <tr>
                    <td>
                        <span class="badge badge-id">{{ $p->idProducto }}</span>
                    </td>
                    <td>
                        <span class="badge badge-cycle">{{ $p->ciclo->ciclo ?? $p->idCiclo }}</span>
                    </td>
                    <td>
                        <div class="product-info">
                            @if($p->franquicia)
                                <span class="product-name">{{ $p->franquicia->franquicia->franquicia ?? 'Sin franquicia' }}</span>
                            @else
                                <span class="product-name">N/A</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="product-info">
                            @if($p->marcaMkt)
                                <span class="product-name">{{ $p->marcaMkt->marca->marca ?? 'Sin marca' }}</span>
                                <span class="product-detail">{{ $p->marcaMkt->mercado->mercado ?? 'Sin mercado' }}</span>
                            @else
                                <span class="product-name">N/A</span>
                            @endif
                        </div>
                    </td>
                    <td>{{ $p->core->core ?? $p->idCore }}</td>
                    <td>{{ $p->cuota->cuota ?? $p->idCuota }}</td>
                    <td>{{ $p->promocion->promocion ?? $p->idPromocion }}</td>
                    <td>{{ $p->alcance->alcance ?? $p->idAlcance }}</td>
                    <td>
                        @if($p->estado && $p->estado->estado == 'Activo')
                            <span class="status status-active">{{ $p->estado->estado }}</span>
                        @else
                            <span class="status status-inactive">{{ $p->estado->estado ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="date-badge">{{ optional($p->fechaModificacion)->format('d/m/Y') ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="date-badge">{{ optional($p->fechaCierre)->format('d/m/Y') ?? '-' }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="empty-state">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                        <h3>No hay productos disponibles</h3>
                        <p>No se encontraron productos con los filtros seleccionados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

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

    {{-- Footer Info --}}
    <div class="info-footer">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 16v-4M12 8h.01"/>
        </svg>
        <span>Origen: ODS.TAB_PRODUCTO</span>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/productos.css')
@endpush

@push('scripts')
@vite('resources/js/productos.js')
@endpush
