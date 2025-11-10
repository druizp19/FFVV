<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UbigeoController extends Controller
{
    /**
     * Busca ubigeos por término de búsqueda.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $geosegmentoId = $request->get('geosegmento');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'El término de búsqueda debe tener al menos 2 caracteres.',
                'data' => []
            ]);
        }

        try {
            // Buscar TODOS los ubigeos que coincidan con la búsqueda
            // Sin importar si ya están asignados a otro geosegmento
            $ubigeos = \DB::table('ODS.TAB_UBIGEO')
                ->where(function ($q) use ($query) {
                    $q->where('departamento', 'like', "%{$query}%")
                      ->orWhere('provincia', 'like', "%{$query}%")
                      ->orWhere('distrito', 'like', "%{$query}%");
                })
                ->select('idUbigeo', 'ubigeo', 'departamento', 'provincia', 'distrito', 'idGeosegmento')
                ->orderBy('departamento')
                ->orderBy('provincia')
                ->orderBy('distrito')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $ubigeos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar ubigeos: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
