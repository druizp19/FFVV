<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Models\Ciclo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Muestra la vista principal del dashboard.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $idCiclo = $request->input('ciclo');
        
        $data = $this->dashboardService->getDashboardData($idCiclo);
        $ciclos = Ciclo::with('estado')->orderBy('idCiclo', 'desc')->get();
        
        return view('dashboard.index', array_merge($data, [
            'ciclos' => $ciclos,
            'cicloSeleccionado' => $idCiclo
        ]));
    }

    /**
     * Obtiene datos para gráficas (AJAX).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChartData(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo');
        $idCiclo = $request->input('ciclo');

        $data = match($tipo) {
            'empleados' => $this->dashboardService->getChartEmpleadosPorZona($idCiclo),
            'geosegmentos' => $this->dashboardService->getChartGeosegmentosPorZona($idCiclo),
            'acciones' => $this->dashboardService->getChartAcciones($idCiclo),
            default => []
        };

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Obtiene estadísticas de un ciclo específico.
     *
     * @param int $idCiclo
     * @return JsonResponse
     */
    public function getEstadisticasCiclo(int $idCiclo): JsonResponse
    {
        $estadisticas = $this->dashboardService->getEstadisticasCiclo($idCiclo);

        return response()->json([
            'success' => true,
            'data' => $estadisticas
        ]);
    }
}
