<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BrickController extends Controller
{
    /**
     * Muestra la vista principal de bricks.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Obtener el ciclo activo por defecto
        $cicloActivo = \DB::table('ODS.TAB_CICLO')
            ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
            ->first();

        // Determinar el ciclo seleccionado (por defecto el activo)
        $cicloSeleccionado = $request->input('ciclo', $cicloActivo->idCiclo ?? null);

        // Obtener todos los ciclos para el filtro
        $ciclos = \DB::table('ODS.TAB_CICLO as c')
            ->join('ODS.TAB_PERIODO_CICLO as pc', 'c.idCiclo', '=', 'pc.idCiclo')
            ->select(
                'c.idCiclo',
                'c.ciclo',
                'c.fechaInicio',
                'c.fechaFin',
                \DB::raw('CASE WHEN GETDATE() BETWEEN c.fechaInicio AND c.fechaFin THEN 1 ELSE 0 END as esActivo')
            )
            ->distinct()
            ->orderBy('c.fechaInicio', 'desc')
            ->get();

        // Obtener departamentos únicos
        $departamentos = \DB::table('BD_McdoFarmaceutico.ddd.MAE_BRICK')
            ->select('Departamento')
            ->whereNotNull('Departamento')
            ->where('Departamento', '!=', '')
            ->distinct()
            ->orderBy('Departamento')
            ->pluck('Departamento');

        $query = \DB::table('ODS.TAB_BRICK_GEOSEGMENTO as bg')
            ->join('BD_McdoFarmaceutico.ddd.MAE_BRICK as b', 'bg.idbrick', '=', 'b.id')
            ->join('ODS.TAB_GEOSEGMENTO as g', 'bg.idgeosegmento', '=', 'g.idGeosegmento')
            ->join('ODS.TAB_PERIODO_CICLO as pc', 'bg.idperiodociclo', '=', 'pc.idPeriodoCiclo')
            ->join('ODS.TAB_CICLO as ci', 'pc.idCiclo', '=', 'ci.idCiclo')
            ->join('ODS.TAB_ESTADO as e', 'bg.idestado', '=', 'e.idEstado')
            ->leftJoin('ODS.TAB_UBIGEO_PERIODO as up', function($join) {
                $join->on('g.idGeosegmento', '=', 'up.idGeosegmento')
                     ->on('bg.idperiodociclo', '=', 'up.idPeriodoCiclo');
            })
            ->select(
                'bg.idbrickgeosegmento',
                'bg.idbrick',
                'b.Código_Brick as codigoBrick',
                'b.Descripción_Brick as descripcionBrick',
                'b.Departamento as departamento',
                'b.Lima_Provincias as provincia',
                'b.Dpto_Distrito as distrito',
                'bg.idgeosegmento',
                'g.geosegmento',
                'bg.idperiodociclo',
                'ci.idCiclo',
                'ci.ciclo',
                'bg.idestado',
                'e.estado',
                \DB::raw('COUNT(DISTINCT up.id) as total_ubigeos')
            )
            ->groupBy(
                'bg.idbrickgeosegmento',
                'bg.idbrick',
                'b.Código_Brick',
                'b.Descripción_Brick',
                'b.Departamento',
                'b.Lima_Provincias',
                'b.Dpto_Distrito',
                'bg.idgeosegmento',
                'g.geosegmento',
                'bg.idperiodociclo',
                'ci.idCiclo',
                'ci.ciclo',
                'bg.idestado',
                'e.estado'
            );

        // Filtro por ciclo
        if ($cicloSeleccionado) {
            $query->where('ci.idCiclo', $cicloSeleccionado);
        }

        // Filtro por departamento
        if ($request->has('departamento') && $request->departamento != '') {
            $query->where('b.Departamento', $request->departamento);
        }

        // Búsqueda global
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('b.Código_Brick', 'like', "%{$search}%")
                  ->orWhere('b.Descripción_Brick', 'like', "%{$search}%")
                  ->orWhere('b.Departamento', 'like', "%{$search}%")
                  ->orWhere('b.Lima_Provincias', 'like', "%{$search}%")
                  ->orWhere('b.Dpto_Distrito', 'like', "%{$search}%")
                  ->orWhere('g.geosegmento', 'like', "%{$search}%")
                  ->orWhere('ci.ciclo', 'like', "%{$search}%");
            });
        }

        $bricks = $query->orderBy('bg.idbrickgeosegmento', 'desc')
            ->paginate(15)
            ->appends($request->all());

        // Verificar si el ciclo seleccionado está activo
        $cicloEsActivo = false;
        if ($cicloSeleccionado) {
            $cicloInfo = \DB::table('ODS.TAB_CICLO')
                ->where('idCiclo', $cicloSeleccionado)
                ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
                ->exists();
            $cicloEsActivo = $cicloInfo;
        }

        return view('bricks.index', compact('bricks', 'ciclos', 'departamentos', 'cicloSeleccionado', 'cicloEsActivo'));
    }

    /**
     * Asigna un brick a un geosegmento en el periodo-ciclo actual.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignBrick(Request $request)
    {
        $validated = $request->validate([
            'idBrick' => 'required|integer',
            'idGeosegmento' => 'required|integer',
        ]);

        try {
            // Obtener el ciclo abierto actual
            $cicloAbierto = \DB::table('ODS.TAB_CICLO')
                ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
                ->first();

            if (!$cicloAbierto) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay un ciclo abierto actualmente.'
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
                    'message' => 'No se encontró un periodo-ciclo activo.'
                ], 400);
            }

            // Obtener información del brick
            $brick = \DB::table('BD_McdoFarmaceutico.ddd.MAE_BRICK')
                ->where('id', $validated['idBrick'])
                ->first();

            if (!$brick) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el brick especificado.'
                ], 404);
            }

            // Obtener los IDs de los canales C e I
            $canales = \DB::table('DTM_MERCADOS.ODS.TAB_CANAL')
                ->whereIn('canal', ['C', 'I'])
                ->where('idEstado', 1)
                ->pluck('idCanal', 'canal')
                ->toArray();

            if (count($canales) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron los canales C e I activos en el sistema.'
                ], 400);
            }

            $insertados = 0;
            $yaExistentes = 0;

            // Insertar o actualizar para ambos canales (C e I)
            foreach ($canales as $canalNombre => $idCanal) {
                // Verificar si ya existe esta combinación
                $existente = \DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                    ->where('idbrick', $validated['idBrick'])
                    ->where('idcanal', $idCanal)
                    ->where('idgeosegmento', $validated['idGeosegmento'])
                    ->where('idperiodociclo', $periodoCiclo->idPeriodoCiclo)
                    ->first();

                if ($existente) {
                    // Si existe pero está inactivo, reactivarlo
                    if ($existente->idestado != 1) {
                        \DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                            ->where('idbrickgeosegmento', $existente->idbrickgeosegmento)
                            ->update([
                                'idestado' => 1,
                                'fechaproceso' => now()
                            ]);
                        $insertados++;
                    } else {
                        $yaExistentes++;
                    }
                } else {
                    // Insertar nuevo registro
                    \DB::table('ODS.TAB_BRICK_GEOSEGMENTO')->insert([
                        'idbrick' => $validated['idBrick'],
                        'idcanal' => $idCanal,
                        'idgeosegmento' => $validated['idGeosegmento'],
                        'idperiodociclo' => $periodoCiclo->idPeriodoCiclo,
                        'fechaproceso' => now(),
                        'idestado' => 1
                    ]);
                    $insertados++;
                }
            }

            // Registrar en el historial
            \App\Models\Historial::create([
                'idCiclo' => $cicloAbierto->idCiclo,
                'entidad' => 'BrickGeosegmento',
                'idEntidad' => $validated['idBrick'],
                'accion' => 'Asignar',
                'descripcion' => sprintf(
                    'Se asignó el brick "%s" (ID %d) al geosegmento ID %d para canales C e I en el ciclo %s',
                    $brick->Descripción_Brick ?? $brick->Código_Brick ?? 'N/A',
                    $validated['idBrick'],
                    $validated['idGeosegmento'],
                    $cicloAbierto->ciclo
                ),
                'datosNuevos' => [
                    'idBrick' => $validated['idBrick'],
                    'idGeosegmento' => $validated['idGeosegmento'],
                    'codigoBrick' => $brick->Código_Brick ?? null,
                    'descripcion' => $brick->Descripción_Brick ?? null,
                    'canales' => ['C', 'I'],
                    'insertados' => $insertados,
                    'yaExistentes' => $yaExistentes
                ],
                'usuario' => session('azure_user.name') ?? 'Sistema',
                'fechaHora' => now(),
            ]);

            if ($insertados > 0) {
                $mensaje = "Brick asignado exitosamente para {$insertados} canal(es).";
                if ($yaExistentes > 0) {
                    $mensaje .= " {$yaExistentes} canal(es) ya estaban asignados.";
                }
                return response()->json([
                    'success' => true,
                    'message' => $mensaje
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Este brick ya está asignado a este geosegmento para ambos canales.'
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Error al asignar brick: ' . $e->getMessage(), [
                'brick' => $validated['idBrick'] ?? null,
                'geosegmento' => $validated['idGeosegmento'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al asignar brick: ' . $e->getMessage()
            ], 500);
        }
    }
}
