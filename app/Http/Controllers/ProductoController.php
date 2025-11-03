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

        if ($request->filled('estado')) {
            $query->where('idEstado', $request->estado);
        }

        $productos = $query->orderBy('idProducto', 'desc')
            ->paginate(10)
            ->appends($request->except('page'));

        $estados = \App\Models\Estado::all();
        $ciclos = \App\Models\Ciclo::orderBy('idCiclo', 'desc')->get();

        return view('productos.index', compact('productos', 'estados', 'ciclos'));
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
            'idCuota' => 'sometimes|required|integer',
            'idPromocion' => 'sometimes|required|integer',
            'idAlcance' => 'sometimes|required|integer',
            'idEstado' => 'sometimes|required|integer',
            'fechaModificacion' => 'nullable|date',
            'fechaCierre' => 'nullable|date',
        ]);

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
        $result = $this->productoService->actualizarProducto($id, [
            'idEstado' => 0
        ]);

        if ($result['success']) {
            $result['message'] = 'Producto desactivado exitosamente.';
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}


