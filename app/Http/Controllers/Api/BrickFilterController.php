<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BrickFilterController extends Controller
{
    /**
     * Obtiene los valores únicos de filtros (departamentos, provincias, distritos).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilters(Request $request): JsonResponse
    {
        try {
            $geosegmento = $request->input('geosegmento');

            if (!$geosegmento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geosegmento es requerido'
                ], 400);
            }

            // Obtener el ciclo abierto actual
            $cicloAbierto = \DB::table('ODS.TAB_CICLO')
                ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
                ->first();

            if (!$cicloAbierto) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay un ciclo abierto actualmente'
                ], 400);
            }

            // Obtener el periodo-ciclo actual
            $periodoCiclo = \DB::table('ODS.TAB_PERIODO_CICLO')
                ->where('idCiclo', $cicloAbierto->idCiclo)
                ->where('idEstado', 1)
                ->first();

            if (!$periodoCiclo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un periodo-ciclo activo'
                ], 400);
            }

            // Obtener bricks ya asignados
            $bricksAsignados = \DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                ->where('idgeosegmento', $geosegmento)
                ->where('idperiodociclo', $periodoCiclo->idPeriodoCiclo)
                ->where('idestado', 1)
                ->pluck('idbrick')
                ->toArray();

            // Obtener valores únicos de filtros
            $departamentos = \DB::table('BD_McdoFarmaceutico.ddd.MAE_BRICK')
                ->whereNotIn('id', $bricksAsignados)
                ->whereNotNull('Departamento')
                ->where('Departamento', '!=', '')
                ->distinct()
                ->pluck('Departamento')
                ->sort()
                ->values();

            $provincias = \DB::table('BD_McdoFarmaceutico.ddd.MAE_BRICK')
                ->whereNotIn('id', $bricksAsignados)
                ->whereNotNull('Lima_Provincias')
                ->where('Lima_Provincias', '!=', '')
                ->distinct()
                ->pluck('Lima_Provincias')
                ->sort()
                ->values();

            $distritos = \DB::table('BD_McdoFarmaceutico.ddd.MAE_BRICK')
                ->whereNotIn('id', $bricksAsignados)
                ->whereNotNull('Dpto_Distrito')
                ->where('Dpto_Distrito', '!=', '')
                ->distinct()
                ->pluck('Dpto_Distrito')
                ->sort()
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'departamentos' => $departamentos,
                    'provincias' => $provincias,
                    'distritos' => $distritos
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener filtros: ' . $e->getMessage()
            ], 500);
        }
    }
}
