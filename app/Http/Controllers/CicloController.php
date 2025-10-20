<?php

namespace App\Http\Controllers;

use App\Services\CicloService;
use App\Http\Requests\CicloRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CicloController extends Controller
{
    protected CicloService $cicloService;

    /**
     * Constructor del controlador.
     *
     * @param CicloService $cicloService
     */
    public function __construct(CicloService $cicloService)
    {
        $this->cicloService = $cicloService;
    }

    /**
     * Muestra la lista de ciclos.
     *
     * @return View
     */
    public function index(): View
    {
        $ciclos = $this->cicloService->getAllCiclos();

        return view('ciclos.index', compact('ciclos'));
    }

    /**
     * Almacena un nuevo ciclo.
     *
     * @param CicloRequest $request
     * @return JsonResponse
     */
    public function store(CicloRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->cicloService->crearCiclo($data);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'ciclo' => $result['data']
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 422);
    }

    /**
     * Muestra un ciclo específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $ciclo = $this->cicloService->getCicloById($id);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ciclo' => $ciclo
        ]);
    }

    /**
     * Obtiene el último ciclo creado.
     *
     * @return JsonResponse
     */
    public function getUltimo(): JsonResponse
    {
        $ciclo = $this->cicloService->getUltimoCiclo();

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'No hay ciclos registrados.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ciclo' => $ciclo
        ]);
    }

    /**
     * Actualiza un ciclo existente.
     *
     * @param CicloRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CicloRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $result = $this->cicloService->actualizarCiclo($id, $data);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 422);
    }

    /**
     * Elimina un ciclo.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->cicloService->eliminarCiclo($id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 404);
    }

    /**
     * Copia la configuración de un ciclo.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function copy(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'ciclo' => 'required|string|max:100',
            'fechaInicio' => 'required|date|date_format:Y-m-d',
            'fechaFin' => 'required|date|date_format:Y-m-d|after:fechaInicio',
        ]);

        $result = $this->cicloService->copiarCiclo($id, $request->only(['ciclo', 'fechaInicio', 'fechaFin']));

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'ciclo' => $result['data'] ?? null
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 422);
    }

    /**
     * Clona un ciclo completo con todas sus relaciones.
     * Copia: Productos, ZonaEmp, ZonaGeo y FuerzaVenta con nuevos IDs.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function copiarCompleto(Request $request, int $id)
    {
        $request->validate([
            'ciclo' => 'required|string|max:255',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after:fechaInicio',
        ]);

        $result = $this->cicloService->copiarCicloCompleto(
            $id, 
            $request->only(['ciclo', 'fechaInicio', 'fechaFin'])
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 422);
    }
}

