<?php

namespace App\Http\Controllers;

use App\Services\EmpleadoService;
use App\Services\CargoService;
use App\Services\AreaService;
use App\Services\UneService;
use App\Services\EstadoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    protected EmpleadoService $empleadoService;
    protected CargoService $cargoService;
    protected AreaService $areaService;
    protected UneService $uneService;
    protected EstadoService $estadoService;

    public function __construct(
        EmpleadoService $empleadoService,
        CargoService $cargoService,
        AreaService $areaService,
        UneService $uneService,
        EstadoService $estadoService
    ) {
        $this->empleadoService = $empleadoService;
        $this->cargoService = $cargoService;
        $this->areaService = $areaService;
        $this->uneService = $uneService;
        $this->estadoService = $estadoService;
    }

    /**
     * Muestra la vista principal de empleados.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Query base con relaciones
        $query = \App\Models\Empleado::with(['cargo', 'area', 'une', 'estado']);
        
        // Aplicar filtros si existen
        if ($request->filled('cargo')) {
            $query->where('idCargo', $request->cargo);
        }
        
        if ($request->filled('area')) {
            $query->where('idArea', $request->area);
        }
        
        if ($request->filled('une')) {
            $query->where('idUne', $request->une);
        }
        
        // Búsqueda por texto (nombre, apellido, DNI)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apeNombre', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }
        
        // Obtener empleados con paginación (15 por página) y mantener parámetros de búsqueda
        $empleados = $query->orderBy('idEmpleado', 'desc')
            ->paginate(15)
            ->appends($request->except('page'));
        
        $cargos = $this->cargoService->getAllCargos();
        $areas = $this->areaService->getAllAreas();
        $unes = $this->uneService->getAllUnes();
        $estados = $this->estadoService->getAllEstados();

        return view('empleados.index', compact('empleados', 'cargos', 'areas', 'unes', 'estados'));
    }

    /**
     * Obtiene todos los empleados en formato JSON.
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $empleados = $this->empleadoService->getAllEmpleados();
        
        return response()->json([
            'success' => true,
            'data' => $empleados
        ]);
    }

    /**
     * Obtiene un empleado por su ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $empleado = $this->empleadoService->getEmpleadoById($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $empleado
        ]);
    }

    /**
     * Crea un nuevo empleado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'idCargo' => 'required|integer',
            'idArea' => 'required|integer',
            'idUne' => 'required|integer',
            'idEstado' => 'required|integer',
            'dni' => 'required|string|max:8|min:8',
            'nombre' => 'required|string|max:100',
            'apeNombre' => 'required|string|max:100',
            'correo' => 'required|email|max:100',
            'celular' => 'nullable|string|max:20',
            'fechaIngreso' => 'required|date',
            'fechaCese' => 'nullable|date|after_or_equal:fechaIngreso',
        ]);

        $result = $this->empleadoService->crearEmpleado($validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Actualiza un empleado existente.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'idCargo' => 'sometimes|required|integer',
            'idArea' => 'sometimes|required|integer',
            'idUne' => 'sometimes|required|integer',
            'idEstado' => 'sometimes|required|integer',
            'dni' => 'sometimes|required|string|max:8|min:8',
            'nombre' => 'sometimes|required|string|max:100',
            'apeNombre' => 'sometimes|required|string|max:100',
            'correo' => 'sometimes|required|email|max:100',
            'celular' => 'nullable|string|max:20',
            'fechaIngreso' => 'sometimes|required|date',
            'fechaCese' => 'nullable|date',
        ]);

        $result = $this->empleadoService->actualizarEmpleado($id, $validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Desactiva un empleado (cambia estado a 2 - Inactivo).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Actualizar el empleado con estado inactivo (idEstado = 0)
        $result = $this->empleadoService->actualizarEmpleado($id, [
            'idEstado' => 0
        ]);

        if ($result['success']) {
            $result['message'] = 'Empleado desactivado exitosamente.';
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Busca empleados por término.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');

        if (empty($termino)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $empleados = $this->empleadoService->buscarEmpleado($termino);

        return response()->json([
            'success' => true,
            'data' => $empleados
        ]);
    }
}

