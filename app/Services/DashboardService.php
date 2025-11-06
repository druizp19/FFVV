<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardService
{
    protected DashboardRepository $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Obtiene todos los datos para el dashboard.
     *
     * @param int|null $idCiclo
     * @return array
     */
    public function getDashboardData(?int $idCiclo = null): array
    {
        return [
            'estadisticas' => $this->dashboardRepository->getEstadisticasGenerales(),
            'empleadosPorZona' => $this->dashboardRepository->getEmpleadosPorZona($idCiclo),
            'geosegmentosPorZona' => $this->dashboardRepository->getGeosegmentosPorZona($idCiclo),
            'accionesPorTipo' => $this->dashboardRepository->getAccionesPorTipo($idCiclo),
            'productosPorCore' => $this->dashboardRepository->getProductosPorCore($idCiclo),
            'actividadPorMes' => $this->dashboardRepository->getActividadPorMes(6),
            'actividadReciente' => $this->dashboardRepository->getActividadReciente(5),
        ];
    }

    /**
     * Obtiene estadísticas específicas de un ciclo.
     *
     * @param int $idCiclo
     * @return array
     */
    public function getEstadisticasCiclo(int $idCiclo): array
    {
        return $this->dashboardRepository->getEstadisticasPorCiclo($idCiclo);
    }

    /**
     * Obtiene datos para gráfica de empleados por zona.
     *
     * @param int|null $idCiclo
     * @return array
     */
    public function getChartEmpleadosPorZona(?int $idCiclo = null): array
    {
        $data = $this->dashboardRepository->getEmpleadosPorZona($idCiclo);
        
        return [
            'labels' => array_column($data, 'zona'),
            'values' => array_column($data, 'total'),
        ];
    }

    /**
     * Obtiene datos para gráfica de geosegmentos por zona.
     *
     * @param int|null $idCiclo
     * @return array
     */
    public function getChartGeosegmentosPorZona(?int $idCiclo = null): array
    {
        $data = $this->dashboardRepository->getGeosegmentosPorZona($idCiclo);
        
        return [
            'labels' => array_column($data, 'zona'),
            'values' => array_column($data, 'total'),
        ];
    }

    /**
     * Obtiene datos para gráfica de acciones.
     *
     * @param int|null $idCiclo
     * @return array
     */
    public function getChartAcciones(?int $idCiclo = null): array
    {
        $data = $this->dashboardRepository->getAccionesPorTipo($idCiclo);
        
        return [
            'labels' => array_column($data, 'accion'),
            'values' => array_column($data, 'total'),
        ];
    }
}
