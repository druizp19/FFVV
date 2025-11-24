<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FranqLineaController extends Controller
{
    /**
     * Obtiene todas las franquicias/líneas disponibles para un ciclo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $cicloId = $request->get('ciclo');
            
            if (!$cicloId) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ciclo es requerido.'
                ], 400);
            }
            
            // Obtener franqlineas que tienen productos en el ciclo especificado
            // Hacer joins con las tablas relacionadas para obtener los nombres
            $franqLineas = \DB::table('ODS.TAB_FRANQLINEA as fl')
                ->join('ODS.TAB_PRODUCTO as p', 'fl.idFranqLinea', '=', 'p.idFranqLinea')
                ->leftJoin('ODS.TAB_MIXTA as m', 'fl.idMixta', '=', 'm.idMixta')
                ->leftJoin('ODS.TAB_ESTRUCTURA as e', 'fl.idEstructura', '=', 'e.idEstructura')
                ->leftJoin('ODS.TAB_LINEA as l', 'fl.idLinea', '=', 'l.idLinea')
                ->where('p.idCiclo', $cicloId)
                ->where('p.idEstado', 1)
                ->select(
                    'fl.idFranqLinea',
                    'm.descripcion',
                    'e.estructura',
                    'l.linea'
                )
                ->groupBy('fl.idFranqLinea', 'm.descripcion', 'e.estructura', 'l.linea')
                ->orderBy('m.descripcion')
                ->orderBy('e.estructura')
                ->orderBy('l.linea')
                ->get()
                ->map(function($fl) {
                    // Concatenar los nombres con guion
                    $partes = array_filter([
                        $fl->descripcion,
                        $fl->estructura,
                        $fl->linea
                    ]);
                    
                    return [
                        'idFranqLinea' => $fl->idFranqLinea,
                        'franqLinea' => implode(' - ', $partes)
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $franqLineas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener franquicias/líneas: ' . $e->getMessage()
            ], 500);
        }
    }
}
