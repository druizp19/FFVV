<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FranqLineaController extends Controller
{
    /**
     * Muestra la vista principal de FranqLinea.
     */
    public function index(Request $request): View
    {
        $query = \DB::table('ODS.TAB_FRANQLINEA as fl')
            ->leftJoin('ODS.TAB_MIXTA as m', 'fl.idMixta', '=', 'm.idMixta')
            ->leftJoin('ODS.TAB_ESTRUCTURA as e', 'fl.idEstructura', '=', 'e.idEstructura')
            ->leftJoin('ODS.TAB_LINEA as l', 'fl.idLinea', '=', 'l.idLinea')
            ->leftJoin('ODS.TAB_ESTADO as est', 'fl.idEstado', '=', 'est.idEstado')
            ->select(
                'fl.idFranqLinea',
                'm.descripcion as mixta',
                'e.estructura',
                'l.linea',
                'est.estado'
            )
            ->orderBy('fl.idFranqLinea', 'desc');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('l.linea', 'like', "%{$search}%")
                  ->orWhere('m.descripcion', 'like', "%{$search}%")
                  ->orWhere('e.estructura', 'like', "%{$search}%");
            });
        }

        $franqlineas = $query->paginate(15)->appends($request->all());

        // Obtener datos para los selectores (solo activos)
        $estructuras = \DB::table('ODS.TAB_ESTRUCTURA')
            ->where('idEstado', 1)
            ->orderBy('estructura')
            ->get();
        $estados = \DB::table('ODS.TAB_ESTADO')->get();

        return view('franqlinea.index', compact('franqlineas', 'estructuras', 'estados'));
    }

    /**
     * Crea una nueva FranqLinea (y opcionalmente Mixta y Línea).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'mixta' => 'required|string|max:100',
                'linea' => 'required|string|max:100',
                'idEstructura' => 'required|integer'
            ]);

            \DB::beginTransaction();

            // 1. Crear o buscar la Mixta
            $mixta = \DB::table('ODS.TAB_MIXTA')
                ->where('descripcion', $request->mixta)
                ->first();

            if (!$mixta) {
                $idMixta = \DB::table('ODS.TAB_MIXTA')->insertGetId([
                    'descripcion' => $request->mixta,
                    'idEstado' => 1
                ]);
            } else {
                $idMixta = $mixta->idMixta;
            }

            // 2. Crear o buscar la Línea
            $linea = \DB::table('ODS.TAB_LINEA')
                ->where('linea', $request->linea)
                ->first();

            if (!$linea) {
                $idLinea = \DB::table('ODS.TAB_LINEA')->insertGetId([
                    'linea' => $request->linea,
                    'idEstado' => 1
                ]);
            } else {
                $idLinea = $linea->idLinea;
            }

            // 3. Crear la FranqLinea
            $idFranqLinea = \DB::table('ODS.TAB_FRANQLINEA')->insertGetId([
                'idMixta' => $idMixta,
                'idEstructura' => $request->idEstructura,
                'idLinea' => $idLinea,
                'idEstado' => 1
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FranqLinea creada exitosamente.',
                'data' => [
                    'idFranqLinea' => $idFranqLinea,
                    'idMixta' => $idMixta,
                    'idLinea' => $idLinea
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear FranqLinea: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Actualiza el estado de una FranqLinea.
     */
    public function updateEstado(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'idEstado' => 'required|integer'
            ]);

            \DB::table('ODS.TAB_FRANQLINEA')
                ->where('idFranqLinea', $id)
                ->update(['idEstado' => $request->idEstado]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 400);
        }
    }
}
