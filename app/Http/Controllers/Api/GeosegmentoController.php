<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GeosegmentoController extends Controller
{
    /**
     * Obtiene todos los geosegmentos activos.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $geosegmentos = \DB::table('ODS.TAB_GEOSEGMENTO')
                ->select('idGeosegmento', 'geosegmento')
                ->where('idEstado', 1)
                ->orderBy('geosegmento')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $geosegmentos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener geosegmentos: ' . $e->getMessage()
            ], 500);
        }
    }
}
