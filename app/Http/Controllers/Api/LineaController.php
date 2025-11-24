<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LineaController extends Controller
{
    /**
     * Obtiene todas las líneas activas.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $lineas = \DB::table('ODS.TAB_LINEA')
                ->orderBy('linea')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $lineas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las líneas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
