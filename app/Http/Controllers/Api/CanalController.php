<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CanalController extends Controller
{
    /**
     * Obtiene todos los canales activos.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $canales = \DB::table('DTM_MERCADOS.ODS.TAB_CANAL')
                ->select('idcanal', 'canal')
                ->where('idEstado', 1)
                ->orderBy('canal')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $canales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener canales: ' . $e->getMessage()
            ], 500);
        }
    }
}
