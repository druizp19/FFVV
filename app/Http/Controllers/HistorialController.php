<?php

namespace App\Http\Controllers;

use App\Services\HistorialService;
use App\Models\Ciclo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HistorialController extends Controller
{
    protected HistorialService $historialService;

    public function __construct(HistorialService $historialService)
    {
        $this->historialService = $historialService;
    }

    /**
     * Muestra la vista principal del historial.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filtros = [
            'ciclo' => $request->input('ciclo'),
            'entidad' => $request->input('entidad'),
            'accion' => $request->input('accion'),
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
            'search' => $request->input('search'),
        ];

        $historial = $this->historialService->getHistorial($filtros, 5);
        $ciclos = Ciclo::orderBy('idCiclo', 'desc')->get();
        $entidades = $this->historialService->getEntidadesDisponibles();
        $acciones = $this->historialService->getAccionesDisponibles();

        return view('historial.index', compact('historial', 'ciclos', 'entidades', 'acciones', 'filtros'));
    }

    /**
     * Obtiene el historial por ciclo (JSON).
     *
     * @param int $idCiclo
     * @return JsonResponse
     */
    public function porCiclo(int $idCiclo): JsonResponse
    {
        $historial = $this->historialService->getHistorialPorCiclo($idCiclo);

        return response()->json([
            'success' => true,
            'data' => $historial
        ]);
    }

    /**
     * Obtiene el historial de una entidad específica (JSON).
     *
     * @param string $entidad
     * @param int $idEntidad
     * @return JsonResponse
     */
    public function porEntidad(string $entidad, int $idEntidad): JsonResponse
    {
        $historial = $this->historialService->getHistorialPorEntidad($entidad, $idEntidad);

        return response()->json([
            'success' => true,
            'data' => $historial
        ]);
    }

    /**
     * Obtiene estadísticas del historial por ciclo (JSON).
     *
     * @param int $idCiclo
     * @return JsonResponse
     */
    public function estadisticas(int $idCiclo): JsonResponse
    {
        $estadisticas = $this->historialService->getEstadisticasPorCiclo($idCiclo);

        return response()->json([
            'success' => true,
            'data' => $estadisticas
        ]);
    }

    /**
     * Registra un evento en el historial (JSON).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registrar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entidad' => 'required|string|max:100',
            'accion' => 'required|string|max:50',
            'descripcion' => 'required|string',
            'idCiclo' => 'nullable|integer',
            'idEntidad' => 'nullable|integer',
            'datosAnteriores' => 'nullable|array',
            'datosNuevos' => 'nullable|array',
        ]);

        $result = $this->historialService->registrarEvento(
            $validated['entidad'],
            $validated['accion'],
            $validated['descripcion'],
            [
                'idCiclo' => $validated['idCiclo'] ?? null,
                'idEntidad' => $validated['idEntidad'] ?? null,
                'datosAnteriores' => $validated['datosAnteriores'] ?? null,
                'datosNuevos' => $validated['datosNuevos'] ?? null,
            ]
        );

        return response()->json($result, $result['success'] ? 201 : 400);
    }
}
