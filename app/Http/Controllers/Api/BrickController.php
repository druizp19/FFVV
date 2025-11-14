<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BrickController extends Controller
{
    /**
     * Busca bricks disponibles para asignar a un geosegmento.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $geosegmento = $request->input('geosegmento');
            $searchTerm = $request->input('q', '');
            $departamento = $request->input('departamento', '');
            $provincia = $request->input('provincia', '');
            $distrito = $request->input('distrito', '');

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

            // Obtener bricks ya asignados a este geosegmento en el periodo actual
            $bricksAsignados = \DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                ->where('idgeosegmento', $geosegmento)
                ->where('idperiodociclo', $periodoCiclo->idPeriodoCiclo)
                ->where('idestado', 1)
                ->pluck('idbrick')
                ->toArray();

            // Buscar bricks disponibles
            $query = \DB::table('BD_McdoFarmaceutico.ddd.MAE_BRICK')
                ->select(
                    'id', 
                    'Código_Brick as brick', 
                    'Descripción_Brick', 
                    'Departamento as departamento', 
                    'Lima_Provincias as provincia', 
                    'Dpto_Distrito as distrito'
                )
                ->whereNotIn('id', $bricksAsignados);

            // Aplicar filtros de ubicación
            if ($departamento) {
                $query->where('Departamento', $departamento);
            }

            if ($provincia) {
                $query->where('Lima_Provincias', $provincia);
            }

            if ($distrito) {
                $query->where('Dpto_Distrito', $distrito);
            }

            // Aplicar búsqueda por texto
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('Código_Brick', 'like', "%{$searchTerm}%")
                      ->orWhere('Descripción_Brick', 'like', "%{$searchTerm}%");
                });
            }

            $bricks = $query->orderBy('Descripción_Brick')
                ->limit(200)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $bricks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar bricks: ' . $e->getMessage()
            ], 500);
        }
    }
}
