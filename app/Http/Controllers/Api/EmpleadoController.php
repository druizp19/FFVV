<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmpleadoController extends Controller
{
    /**
     * Busca empleados por nombre o código.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->query('q', '');
            
            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $search = '%' . $searchTerm . '%';
            
            $empleados = \DB::table('ODS.TAB_EMPLEADO')
                ->where('idEstado', 1)
                ->where(function($query) use ($search) {
                    $query->whereRaw("CONCAT(nombre, ' ', ISNULL(apeNombre, '')) LIKE ?", [$search])
                          ->orWhere('dni', 'LIKE', $search);
                })
                ->select(
                    'idEmpleado',
                    \DB::raw("CONCAT(nombre, ' ', ISNULL(apeNombre, '')) as nombre"),
                    'idcargo',
                    'dni'
                )
                ->limit(20)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $empleados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar empleados: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Obtiene un empleado por su ID con sus fechas.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $empleado = \DB::table('ODS.TAB_EMPLEADO')
                ->where('idEmpleado', $id)
                ->first();

            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado.',
                    'data' => null
                ], 404);
            }

            // Formatear fechas si existen
            $data = [
                'idEmpleado' => $empleado->idEmpleado,
                'nombre' => $empleado->nombre,
                'apeNombre' => $empleado->apeNombre ?? nullf,
                'fechaIngreso' => $empleado->fechaIngreso ? date('Y-m-d', strtotime($empleado->fechaIngreso)) : null,
                'fechaCese' => $empleado->fechaCese ? date('Y-m-d', strtotime($empleado->fechaCese)) : null,
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el empleado: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
