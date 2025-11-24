<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ZonaController extends Controller
{
    /**
     * Obtiene todas las zonas con su conteo de geosegmentos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Obtener el ciclo abierto actual
            $cicloAbierto = \App\Models\Ciclo::with('estado')
                ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
                ->first();

            if (!$cicloAbierto) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay un ciclo abierto actualmente.',
                    'data' => []
                ]);
            }

            // Obtener zonas con conteo de geosegmentos
            $zonas = \App\Models\Zona::with('estado')
                ->leftJoin('ODS.TAB_ZONAGEO as zg', function($join) use ($cicloAbierto) {
                    $join->on('ODS.TAB_ZONA.idZona', '=', 'zg.idZona')
                         ->where('zg.idCiclo', '=', $cicloAbierto->idCiclo)
                         ->where('zg.idEstado', '=', 1);
                })
                ->select(
                    'ODS.TAB_ZONA.idZona',
                    'ODS.TAB_ZONA.zona',
                    'ODS.TAB_ZONA.idEstado',
                    \DB::raw('COUNT(DISTINCT zg.idGeosegmento) as geosegmentos_count')
                )
                ->groupBy('ODS.TAB_ZONA.idZona', 'ODS.TAB_ZONA.zona', 'ODS.TAB_ZONA.idEstado')
                ->orderBy('ODS.TAB_ZONA.zona')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $zonas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las zonas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Cuenta los geosegmentos únicos de múltiples zonas.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function countUniqueGeosegmentos(Request $request): JsonResponse
    {
        try {
            $zonasIds = $request->get('zonas');
            $cicloId = $request->get('ciclo');

            if (!$zonasIds) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes proporcionar al menos una zona.',
                    'count' => 0
                ]);
            }

            // Convertir string de IDs separados por coma a array
            $zonasArray = explode(',', $zonasIds);
            $zonasArray = array_map('intval', $zonasArray);

            // Contar geosegmentos únicos (DISTINCT) de las zonas seleccionadas
            $query = \DB::table('ODS.TAB_ZONAGEO')
                ->whereIn('idZona', $zonasArray)
                ->where('idEstado', 1);

            if ($cicloId) {
                $query->where('idCiclo', $cicloId);
            }

            $count = $query->distinct()->count('idGeosegmento');

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al contar geosegmentos: ' . $e->getMessage(),
                'count' => 0
            ], 500);
        }
    }

    /**
     * Obtiene las zonas que tienen representantes de una línea específica.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getZonasByLinea(Request $request): JsonResponse
    {
        try {
            $idLinea = $request->get('linea');
            $cicloId = $request->get('ciclo');

            if (!$idLinea) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes proporcionar una línea.',
                    'data' => []
                ]);
            }

            if (!$cicloId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar un ciclo.',
                    'data' => []
                ]);
            }

            // Obtener zonas que tienen representantes de la línea seleccionada
            $query = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_PRODUCTO as p', 'fv.idProducto', '=', 'p.idProducto')
                ->join('ODS.TAB_FRANQLINEA as fl', 'p.idFranqLinea', '=', 'fl.idFranqLinea')
                ->join('ODS.TAB_ZONAEMP as ze', 'fv.idZonaEmp', '=', 'ze.idZonaEmp')
                ->where('fl.idLinea', $idLinea)
                ->where('fv.idCiclo', $cicloId)
                ->where('fv.idEstado', 1)
                ->where('ze.idEstado', 1);

            // Log para debug
            \Log::info('Query SQL para filtro de línea:', [
                'sql' => $query->toSql(),
                'bindings' => [
                    'idLinea' => $idLinea,
                    'idCiclo' => $cicloId
                ]
            ]);

            $zonasIds = $query->distinct()->pluck('ze.idZona');

            \Log::info('Zonas encontradas:', [
                'count' => $zonasIds->count(),
                'zonas' => $zonasIds->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $zonasIds->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener zonas por línea: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}

