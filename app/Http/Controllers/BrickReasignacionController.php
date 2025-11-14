<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrickReasignacionController extends Controller
{
    /**
     * Obtiene los bricks de un geosegmento específico.
     */
    public function getBricksGeosegmento(Request $request)
    {
        $validated = $request->validate([
            'idGeosegmento' => 'required|integer',
        ]);

        try {
            // Obtener el periodo-ciclo actual
            $periodoCiclo = DB::table('ODS.TAB_PERIODO_CICLO as pc')
                ->join('ODS.TAB_CICLO as c', 'pc.idCiclo', '=', 'c.idCiclo')
                ->whereRaw('GETDATE() BETWEEN c.fechaInicio AND c.fechaFin')
                ->where('pc.idEstado', 1)
                ->select('pc.idPeriodoCiclo')
                ->first();

            if (!$periodoCiclo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un periodo-ciclo activo.'
                ], 400);
            }

            // Obtener bricks del geosegmento con estado activo
            // Agrupamos por idbrick para evitar duplicados (un brick por cada canal C e I)
            $bricks = DB::table('ODS.TAB_BRICK_GEOSEGMENTO as bg')
                ->join('BD_McdoFarmaceutico.ddd.MAE_BRICK as b', 'bg.idbrick', '=', 'b.id')
                ->where('bg.idgeosegmento', $validated['idGeosegmento'])
                ->where('bg.idperiodociclo', $periodoCiclo->idPeriodoCiclo)
                ->where('bg.idestado', 1)
                ->select(
                    'bg.idbrick',
                    'b.Código_Brick as codigoBrick',
                    'b.Descripción_Brick as descripcionBrick',
                    'b.Departamento as departamento',
                    'b.Lima_Provincias as provincia',
                    'b.Dpto_Distrito as distrito'
                )
                ->groupBy(
                    'bg.idbrick',
                    'b.Código_Brick',
                    'b.Descripción_Brick',
                    'b.Departamento',
                    'b.Lima_Provincias',
                    'b.Dpto_Distrito'
                )
                ->orderBy('b.Código_Brick')
                ->get();

            return response()->json([
                'success' => true,
                'bricks' => $bricks
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener bricks del geosegmento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los bricks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los geosegmentos disponibles para reasignación (excluyendo el origen).
     */
    public function getGeosegmentosDestino(Request $request)
    {
        $validated = $request->validate([
            'idGeosegmentoOrigen' => 'required|integer',
        ]);

        try {
            $geosegmentos = DB::table('ODS.TAB_GEOSEGMENTO')
                ->where('idEstado', 1)
                ->where('idGeosegmento', '!=', $validated['idGeosegmentoOrigen'])
                ->select('idGeosegmento', 'geosegmento')
                ->orderBy('geosegmento')
                ->get();

            return response()->json([
                'success' => true,
                'geosegmentos' => $geosegmentos
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener geosegmentos destino: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los geosegmentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reasigna bricks de un geosegmento a otro.
     */
    public function reasignarBricks(Request $request)
    {
        $validated = $request->validate([
            'idGeosegmentoOrigen' => 'required|integer',
            'idGeosegmentoDestino' => 'required|integer',
            'bricks' => 'required|array',
            'bricks.*' => 'integer',
        ]);

        DB::beginTransaction();

        try {
            // Obtener el periodo-ciclo actual
            $periodoCiclo = DB::table('ODS.TAB_PERIODO_CICLO as pc')
                ->join('ODS.TAB_CICLO as c', 'pc.idCiclo', '=', 'c.idCiclo')
                ->whereRaw('GETDATE() BETWEEN c.fechaInicio AND c.fechaFin')
                ->where('pc.idEstado', 1)
                ->select('pc.*', 'c.ciclo')
                ->first();

            if (!$periodoCiclo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un periodo-ciclo activo.'
                ], 400);
            }

            $geosegmentoOrigen = DB::table('ODS.TAB_GEOSEGMENTO')
                ->where('idGeosegmento', $validated['idGeosegmentoOrigen'])
                ->first();

            $geosegmentoDestino = DB::table('ODS.TAB_GEOSEGMENTO')
                ->where('idGeosegmento', $validated['idGeosegmentoDestino'])
                ->first();

            if (!$geosegmentoOrigen || !$geosegmentoDestino) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geosegmento no encontrado.'
                ], 404);
            }

            $reasignados = 0;
            $errores = [];

            // Obtener los IDs de los canales C e I
            $canales = DB::table('DTM_MERCADOS.ODS.TAB_CANAL')
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

            foreach ($validated['bricks'] as $idBrick) {
                // Para cada brick, procesar ambos canales (C e I)
                foreach ($canales as $canalNombre => $idCanal) {
                    // Verificar si el brick existe en el geosegmento origen para este canal
                    $registroOrigen = DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                        ->select('idbrickgeosegmento', 'idbrick', 'idcanal', 'idgeosegmento', 'idperiodociclo', 'idestado')
                        ->where('idbrick', $idBrick)
                        ->where('idcanal', $idCanal)
                        ->where('idgeosegmento', $validated['idGeosegmentoOrigen'])
                        ->where('idperiodociclo', $periodoCiclo->idPeriodoCiclo)
                        ->where('idestado', 1)
                        ->first();

                    if (!$registroOrigen) {
                        continue; // Si no existe en origen, continuar con el siguiente canal
                    }

                    // Verificar si ya existe en el destino
                    $existeDestino = DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                        ->select('idbrickgeosegmento', 'idbrick', 'idcanal', 'idgeosegmento', 'idperiodociclo', 'idestado')
                        ->where('idbrick', $idBrick)
                        ->where('idcanal', $idCanal)
                        ->where('idgeosegmento', $validated['idGeosegmentoDestino'])
                        ->where('idperiodociclo', $periodoCiclo->idPeriodoCiclo)
                        ->first();

                    if ($existeDestino) {
                        // Si existe pero está inactivo, reactivarlo
                        if ($existeDestino->idestado != 1) {
                            DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                                ->where('idbrickgeosegmento', $existeDestino->idbrickgeosegmento)
                                ->update([
                                    'idestado' => 1,
                                    'fechaproceso' => now()
                                ]);
                        }
                    } else {
                        // Insertar en el geosegmento destino
                        DB::table('ODS.TAB_BRICK_GEOSEGMENTO')->insert([
                            'idbrick' => $idBrick,
                            'idcanal' => $idCanal,
                            'idgeosegmento' => $validated['idGeosegmentoDestino'],
                            'idperiodociclo' => $periodoCiclo->idPeriodoCiclo,
                            'fechaproceso' => now(),
                            'idestado' => 1
                        ]);
                    }

                    // Desactivar el registro en el geosegmento origen
                    DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                        ->where('idbrickgeosegmento', $registroOrigen->idbrickgeosegmento)
                        ->update([
                            'idestado' => 0,
                            'fechaproceso' => now()
                        ]);

                    $reasignados++;
                }
            }

            // Verificar si el geosegmento origen se quedó sin bricks activos
            $bricksRestantes = DB::table('ODS.TAB_BRICK_GEOSEGMENTO')
                ->where('idgeosegmento', $validated['idGeosegmentoOrigen'])
                ->where('idperiodociclo', $periodoCiclo->idPeriodoCiclo)
                ->where('idestado', 1)
                ->count();

            $geosegmentoOrigenDesactivado = false;
            if ($bricksRestantes == 0) {
                // Desactivar el geosegmento origen
                DB::table('ODS.TAB_GEOSEGMENTO')
                    ->where('idGeosegmento', $validated['idGeosegmentoOrigen'])
                    ->update(['idEstado' => 0]);
                $geosegmentoOrigenDesactivado = true;
            }

            // Registrar en el historial
            \App\Models\Historial::create([
                'idCiclo' => $periodoCiclo->idCiclo,
                'entidad' => 'BrickGeosegmento',
                'idEntidad' => $validated['idGeosegmentoOrigen'],
                'accion' => 'Reasignar',
                'descripcion' => sprintf(
                    'Se reasignaron %d brick(s) de "%s" a "%s" en el ciclo %s',
                    count($validated['bricks']),
                    $geosegmentoOrigen->geosegmento,
                    $geosegmentoDestino->geosegmento,
                    $periodoCiclo->ciclo
                ),
                'datosNuevos' => [
                    'geosegmentoOrigen' => $geosegmentoOrigen->geosegmento,
                    'geosegmentoDestino' => $geosegmentoDestino->geosegmento,
                    'bricksReasignados' => $validated['bricks'],
                    'totalReasignados' => $reasignados,
                    'geosegmentoOrigenDesactivado' => $geosegmentoOrigenDesactivado,
                    'errores' => $errores
                ],
                'usuario' => session('azure_user.name') ?? 'Sistema',
                'fechaHora' => now(),
            ]);

            DB::commit();

            $mensaje = "Se reasignaron exitosamente {$reasignados} registro(s) de brick(s).";
            if ($geosegmentoOrigenDesactivado) {
                $mensaje .= " El geosegmento origen '{$geosegmentoOrigen->geosegmento}' se desactivó porque no tiene bricks restantes.";
            }
            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode(', ', $errores);
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'reasignados' => $reasignados,
                'geosegmentoOrigenDesactivado' => $geosegmentoOrigenDesactivado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al reasignar bricks: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al reasignar bricks: ' . $e->getMessage()
            ], 500);
        }
    }
}
