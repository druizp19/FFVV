<?php

namespace App\Repositories;

use App\Models\Ciclo;
use App\Models\Zona;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Historial;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    /**
     * Obtiene estadísticas generales del sistema.
     *
     * @return array
     */
    public function getEstadisticasGenerales(): array
    {
        return [
            'totalCiclos' => Ciclo::count(),
            'ciclosActivos' => Ciclo::whereHas('estado', function($q) {
                $q->where('estado', 'Activo');
            })->count(),
            'totalZonas' => Zona::where('idEstado', 1)->count(),
            'totalEmpleados' => Empleado::where('idEstado', 1)->count(),
            'totalProductos' => Producto::whereHas('estado', function($q) {
                $q->where('estado', 'Activo');
            })->count(),
        ];
    }

    
    /**
     * Obtiene la actividad reciente del historial.
     *
     * @param int $limit
     * @return array
     */
    public function getActividadReciente(int $limit = 10): array
    {
        return Historial::with(['ciclo'])
            ->orderBy('fechaHora', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Obtiene estadísticas de acciones por tipo.
     *
     * @param int|null $idCiclo
     * @return array
     */
    public function getAccionesPorTipo(?int $idCiclo = null): array
    {
        $query = Historial::select('accion', DB::raw('COUNT(*) as total'))
            ->groupBy('accion')
            ->orderBy('total', 'desc');

        if ($idCiclo) {
            $query->where('idCiclo', $idCiclo);
        }

        return $query->get()->toArray();
    }

    /**
     * Obtiene la distribución de productos por core.
     *
     * @param int|null $idCiclo
     * @return array
     */
    public function getProductosPorCore(?int $idCiclo = null): array
    {
        $query = DB::table('ODS.TAB_PRODUCTO as p')
            ->join('ODS.TAB_CORE as c', 'p.idCore', '=', 'c.idCore')
            ->select('c.core', DB::raw('COUNT(*) as total'))
            ->groupBy('c.core')
            ->orderBy('total', 'desc');

        if ($idCiclo) {
            $query->where('p.idCiclo', $idCiclo);
        }

        return $query->get()->toArray();
    }

    /**
     * Obtiene la evolución de actividad por mes.
     *
     * @param int $meses
     * @return array
     */
    public function getActividadPorMes(int $meses = 6): array
    {
        $fechaInicio = now()->subMonths($meses)->startOfMonth();

        return Historial::select(
                DB::raw('YEAR(fechaHora) as anio'),
                DB::raw('MONTH(fechaHora) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->where('fechaHora', '>=', $fechaInicio)
            ->groupBy(DB::raw('YEAR(fechaHora)'), DB::raw('MONTH(fechaHora)'))
            ->orderBy('anio')
            ->orderBy('mes')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene estadísticas por ciclo.
     *
     * @param int $idCiclo
     * @return array
     */
    public function getEstadisticasPorCiclo(int $idCiclo): array
    {
        return [
            'empleados' => DB::table('ODS.TAB_ZONAEMP')
                ->where('idCiclo', $idCiclo)
                ->where('idEstado', 1)
                ->distinct('idEmpleado')
                ->count('idEmpleado'),
            'zonas' => DB::table('ODS.TAB_ZONAEMP')
                ->where('idCiclo', $idCiclo)
                ->where('idEstado', 1)
                ->distinct('idZona')
                ->count('idZona'),
            'geosegmentos' => DB::table('ODS.TAB_ZONAGEO')
                ->where('idCiclo', $idCiclo)
                ->where('idEstado', 1)
                ->distinct('idGeosegmento')
                ->count('idGeosegmento'),
            'productos' => Producto::where('idCiclo', $idCiclo)->count(),
            'actividad' => Historial::where('idCiclo', $idCiclo)->count(),
        ];
    }

}
