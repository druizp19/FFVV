<?php

namespace App\Http\Controllers;

use App\Services\ZonaService;
use App\Services\GeosegmentoService;
use App\Services\CicloService;
use App\Services\EmpleadoService;
use App\Services\EstadoService;
use App\Services\UbigeoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ZonaController extends Controller
{
    protected ZonaService $zonaService;
    protected GeosegmentoService $geosegmentoService;
    protected CicloService $cicloService;
    protected EmpleadoService $empleadoService;
    protected EstadoService $estadoService;
    protected UbigeoService $ubigeoService;

    public function __construct(
        ZonaService $zonaService,
        GeosegmentoService $geosegmentoService,
        CicloService $cicloService,
        EmpleadoService $empleadoService,
        EstadoService $estadoService,
        UbigeoService $ubigeoService
    ) {
        $this->zonaService = $zonaService;
        $this->geosegmentoService = $geosegmentoService;
        $this->cicloService = $cicloService;
        $this->empleadoService = $empleadoService;
        $this->estadoService = $estadoService;
        $this->ubigeoService = $ubigeoService;
    }

    /**
     * Muestra la vista principal de zonas.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Obtener todos los ciclos con la relación estado cargada
        $ciclos = \App\Models\Ciclo::with('estado')->get();
        
        // Si hay un ciclo seleccionado, obtener datos filtrados por ese ciclo
        $cicloSeleccionado = $request->get('ciclo');
        
        // Query base: SIEMPRE traer todas las zonas
        $query = \App\Models\Zona::with(['estado']);
        
        // Si hay ciclo seleccionado, cargar relaciones filtradas por ciclo
        if ($cicloSeleccionado) {
            $query->with([
                'zonasEmpleados' => function ($q) use ($cicloSeleccionado) {
                    $q->where('idCiclo', $cicloSeleccionado)->with('empleado');
                },
                'zonasGeosegmentos' => function ($q) use ($cicloSeleccionado) {
                    $q->where('idCiclo', $cicloSeleccionado)->with('geosegmento');
                }
            ]);
        } else {
            // Sin ciclo seleccionado, cargar todas las relaciones
            $query->with(['zonasEmpleados.empleado', 'zonasGeosegmentos.geosegmento']);
        }
        
        // Aplicar filtros si existen
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('zona', 'like', "%{$search}%");
        }
        
        // Obtener zonas con paginación
        $zonas = $query->orderBy('idZona', 'desc')
            ->paginate(10)
            ->appends($request->except('page'));
        
        $geosegmentos = $this->geosegmentoService->getAllGeosegmentos();
        $empleados = $this->empleadoService->getAllEmpleados();
        $estados = $this->estadoService->getAllEstados();
        $ubigeos = $this->ubigeoService->getAllUbigeos();

        return view('zonas.index', compact('zonas', 'geosegmentos', 'ciclos', 'empleados', 'estados', 'ubigeos', 'cicloSeleccionado'));
    }

    /**
     * Obtiene una zona por su ID con sus asignaciones.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $zona = $this->zonaService->getZonaById($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $zona
        ]);
    }

    /**
     * Crea una nueva zona.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zona' => 'required|string|max:100',
            'idEstado' => 'sometimes|integer',
            'geosegmentos' => 'array',
            'geosegmentos.*.idGeosegmento' => 'nullable|integer',
            'geosegmentos.*.idCiclo' => 'required|integer',
            'geosegmentos.*.nuevoGeosegmento' => 'nullable|string|max:100',
            'geosegmentos.*.nuevoLugar' => 'nullable|string|max:100',
        ]);

        $result = $this->zonaService->crearZona($validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Actualiza una zona existente.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'zona' => 'sometimes|required|string|max:100',
            'idEstado' => 'sometimes|required|integer',
        ]);

        $result = $this->zonaService->actualizarZona($id, $validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Desactiva una zona (cambia estado a 0).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Actualizar la zona con estado inactivo (idEstado = 0)
        $result = $this->zonaService->actualizarZona($id, [
            'idEstado' => 0
        ]);

        if ($result['success']) {
            $result['message'] = 'Zona desactivada exitosamente.';
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Obtiene los empleados asignados a una zona.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getEmpleados(Request $request, int $id): JsonResponse
    {
        $cicloId = $request->get('ciclo');
        
        $zona = \App\Models\Zona::with([
            'zonasEmpleados' => function ($q) use ($cicloId) {
                if ($cicloId) {
                    $q->where('idCiclo', $cicloId);
                }
            },
            'zonasEmpleados.empleado.cargo',
            'zonasEmpleados.ciclo',
            'zonasEmpleados.estado'
        ])->find($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $zona->zonasEmpleados
        ]);
    }

    /**
     * Obtiene los geosegmentos asignados a una zona.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getGeosegmentos(Request $request, int $id): JsonResponse
    {
        $cicloId = $request->get('ciclo');
        
        $zona = \App\Models\Zona::with([
            'zonasGeosegmentos' => function ($q) use ($cicloId) {
                if ($cicloId) {
                    $q->where('idCiclo', $cicloId);
                }
            },
            'zonasGeosegmentos.geosegmento',
            'zonasGeosegmentos.ciclo',
            'zonasGeosegmentos.estado'
        ])->find($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $zona->zonasGeosegmentos
        ]);
    }

    /**
     * Obtiene los ubigeos relacionados con los geosegmentos de una zona.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getUbigeos(Request $request, int $id): JsonResponse
    {
        $cicloId = $request->get('ciclo');
        
        $zona = \App\Models\Zona::with([
            'zonasGeosegmentos' => function ($q) use ($cicloId) {
                if ($cicloId) {
                    $q->where('idCiclo', $cicloId);
                }
            },
            'zonasGeosegmentos.geosegmento.ubigeos.geosegmento'
        ])->find($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        // Recopilar todos los ubigeos de los geosegmentos asignados
        $ubigeos = collect();
        foreach ($zona->zonasGeosegmentos as $zonaGeo) {
            if ($zonaGeo->geosegmento && $zonaGeo->geosegmento->ubigeos) {
                $ubigeos = $ubigeos->merge($zonaGeo->geosegmento->ubigeos);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $ubigeos->unique('idUbigeo')->values()
        ]);
    }
}

