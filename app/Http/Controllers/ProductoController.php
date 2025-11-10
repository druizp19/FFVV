<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductoController extends Controller
{
    protected ProductoService $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    /**
     * Muestra la vista principal de productos.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = \App\Models\Producto::with([
            'ciclo',
            'franqLinea',
            'franqLinea.franquicia',
            'franqLinea.linea',
            'marcaMkt',
            'marcaMkt.marca',
            'marcaMkt.mercado',
            'core',
            'cuota',
            'promocion',
            'alcance',
            'estado'
        ]);

        if ($request->filled('ciclo')) {
            $query->where('idCiclo', $request->ciclo);
        }

        // Si no se especifica estado, filtrar por activos por defecto
        if ($request->filled('estado')) {
            $query->where('idEstado', $request->estado);
        } else {
            // Filtrar por estado activo por defecto
            $estadoActivo = \App\Models\Estado::where('estado', 'Activo')->first();
            if ($estadoActivo) {
                $query->where('idEstado', $estadoActivo->idEstado);
            }
        }

        if ($request->filled('marca')) {
            $query->whereHas('marcaMkt.marca', function ($q) use ($request) {
                $q->where('idMarca', $request->marca);
            });
        }

        $productos = $query->orderBy('idProducto', 'desc')
            ->paginate(10)
            ->appends($request->except('page'));

        $estados = \App\Models\Estado::all();
        $ciclos = \App\Models\Ciclo::with('estado')->orderBy('idCiclo', 'desc')->get();
        $marcas = \App\Models\Marca::with('estado')
            ->whereHas('estado', function ($q) {
                $q->where('estado', 'Activo');
            })
            ->orderBy('marca', 'asc')
            ->get();
        
        $cuotas = \App\Models\Cuota::orderBy('cuota', 'asc')->get();
        $promociones = \App\Models\Promocion::orderBy('promocion', 'asc')->get();
        $alcances = \App\Models\Alcance::orderBy('alcance', 'asc')->get();
        $cores = \App\Models\Core::orderBy('core', 'asc')->get();

        // Verificar si el ciclo seleccionado está cerrado
        $cicloCerrado = false;
        if ($request->filled('ciclo')) {
            $cicloSeleccionado = \App\Models\Ciclo::with('estado')->find($request->ciclo);
            if ($cicloSeleccionado) {
                // Verificar por fecha de fin
                if ($cicloSeleccionado->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($cicloSeleccionado->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $cicloCerrado = $fechaFin->lt($hoy);
                }
                
                // Verificar por estado
                if (!$cicloCerrado && $cicloSeleccionado->estado) {
                    $cicloCerrado = $cicloSeleccionado->estado->estado === 'Cerrado';
                }
            }
        }

        return view('productos.index', compact('productos', 'estados', 'ciclos', 'marcas', 'cuotas', 'promociones', 'alcances', 'cores', 'cicloCerrado'));
    }

    /**
     * Devuelve todos los productos (JSON).
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $productos = $this->productoService->getAllProductos();

        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }

    /**
     * Muestra un producto por su ID (JSON).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $producto = $this->productoService->getProductoById($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $producto
        ]);
    }

    /**
     * Crea un nuevo producto (JSON).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'idCiclo' => 'required|integer',
            'idFranqLinea' => 'required|integer',
            'idMarcaMkt' => 'required|integer',
            'idCore' => 'required|integer',
            'idCuota' => 'required|integer',
            'idPromocion' => 'required|integer',
            'idAlcance' => 'required|integer',
            'idEstado' => 'required|integer',
            'fechaModificacion' => 'nullable|date',
            'fechaCierre' => 'nullable|date|after_or_equal:fechaModificacion',
        ]);

        // Verificar si el ciclo está cerrado
        $ciclo = \App\Models\Ciclo::with('estado')->find($validated['idCiclo']);
        if ($ciclo) {
            $esCerrado = false;
            
            // Verificar por fecha de fin
            if ($ciclo->fechaFin) {
                $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                $hoy = \Carbon\Carbon::now()->startOfDay();
                $esCerrado = $fechaFin->lt($hoy);
            }
            
            // Verificar por estado
            if (!$esCerrado && $ciclo->estado) {
                $esCerrado = $ciclo->estado->estado === 'Cerrado';
            }
            
            if ($esCerrado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden crear productos en un ciclo cerrado.'
                ], 403);
            }
        }

        $result = $this->productoService->crearProducto($validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Actualiza un producto (JSON).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'idCiclo' => 'sometimes|required|integer',
            'idFranqLinea' => 'sometimes|required|integer',
            'idMarcaMkt' => 'sometimes|required|integer',
            'idCore' => 'sometimes|required|integer',
            'idCuota' => 'nullable|integer',
            'idPromocion' => 'nullable|integer',
            'idAlcance' => 'nullable|integer',
            'idEstado' => 'sometimes|required|integer',
            'fechaModificacion' => 'nullable|date',
            'fechaCierre' => 'nullable|date',
        ]);

        // Obtener el producto actual para verificar su ciclo
        $producto = \App\Models\Producto::with('ciclo.estado')->find($id);
        
        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.'
            ], 404);
        }

        // Verificar si el ciclo del producto está cerrado
        if ($producto->ciclo) {
            $esCerrado = false;
            
            // Verificar por fecha de fin
            if ($producto->ciclo->fechaFin) {
                $fechaFin = \Carbon\Carbon::parse($producto->ciclo->fechaFin)->startOfDay();
                $hoy = \Carbon\Carbon::now()->startOfDay();
                $esCerrado = $fechaFin->lt($hoy);
            }
            
            // Verificar por estado
            if (!$esCerrado && $producto->ciclo->estado) {
                $esCerrado = $producto->ciclo->estado->estado === 'Cerrado';
            }
            
            if ($esCerrado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden modificar productos de un ciclo cerrado.'
                ], 403);
            }
        }

        $result = $this->productoService->actualizarProducto($id, $validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Desactiva un producto (JSON) - idEstado = 0.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Obtener el producto para verificar su ciclo
        $producto = \App\Models\Producto::with('ciclo.estado')->find($id);
        
        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.'
            ], 404);
        }

        // Verificar si el ciclo del producto está cerrado
        if ($producto->ciclo) {
            $esCerrado = false;
            
            // Verificar por fecha de fin
            if ($producto->ciclo->fechaFin) {
                $fechaFin = \Carbon\Carbon::parse($producto->ciclo->fechaFin)->startOfDay();
                $hoy = \Carbon\Carbon::now()->startOfDay();
                $esCerrado = $fechaFin->lt($hoy);
            }
            
            // Verificar por estado
            if (!$esCerrado && $producto->ciclo->estado) {
                $esCerrado = $producto->ciclo->estado->estado === 'Cerrado';
            }
            
            if ($esCerrado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden desactivar productos de un ciclo cerrado.'
                ], 403);
            }
        }

        $result = $this->productoService->actualizarProducto($id, [
            'idEstado' => 0
        ]);

        if ($result['success']) {
            $result['message'] = 'Producto desactivado exitosamente.';
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}


