<?php

namespace App\Http\Controllers;

use App\Services\GeosegmentoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeosegmentoController extends Controller
{
    protected GeosegmentoService $geosegmentoService;

    public function __construct(GeosegmentoService $geosegmentoService)
    {
        $this->geosegmentoService = $geosegmentoService;
    }

    /**
     * Crea un nuevo geosegmento.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'geosegmento' => 'required|string|max:255',
            'lugar' => 'nullable|string|max:255',
        ]);

        // Por defecto, crear con estado activo (idEstado = 1)
        $data = [
            'geosegmento' => $validated['geosegmento'],
            'lugar' => $validated['lugar'] ?? null,
            'idEstado' => 1,
        ];

        $result = $this->geosegmentoService->crearGeosegmento($data);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Muestra la vista principal de geosegmentos.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener todos los ciclos
        $ciclos = \App\Models\Ciclo::with('estado')->orderBy('idCiclo', 'desc')->get();
        
        // Ciclo seleccionado (por defecto el abierto)
        $cicloSeleccionado = $request->get('ciclo');
        
        // Si no hay ciclo seleccionado, buscar el ciclo abierto
        if (!$cicloSeleccionado) {
            $cicloAbierto = $ciclos->filter(function ($ciclo) {
                $esCerrado = false;
                
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                if (!$esCerrado && $ciclo->estado) {
                    $esCerrado = $ciclo->estado->estado === 'Cerrado';
                }
                
                return !$esCerrado;
            })->sortByDesc('idCiclo')->first();
            
            if ($cicloAbierto) {
                $cicloSeleccionado = $cicloAbierto->idCiclo;
            }
        }

        // Obtener el periodo-ciclo del ciclo seleccionado
        $periodoCicloId = null;
        if ($cicloSeleccionado) {
            $periodoCiclo = \DB::table('ODS.TAB_PERIODO_CICLO')
                ->where('idCiclo', $cicloSeleccionado)
                ->where('idEstado', 1)
                ->first();
            
            if ($periodoCiclo) {
                $periodoCicloId = $periodoCiclo->idPeriodoCiclo;
            }
        }

        // Obtener geosegmentos con contador de ubigeos desde TAB_UBIGEO_PERIODO
        $query = \App\Models\Geosegmento::with('estado')
            ->leftJoin('ODS.TAB_UBIGEO_PERIODO as up', function($join) use ($periodoCicloId) {
                $join->on('ODS.TAB_GEOSEGMENTO.idGeosegmento', '=', 'up.idGeosegmento');
                if ($periodoCicloId) {
                    $join->where('up.idPeriodoCiclo', '=', $periodoCicloId);
                }
            })
            ->select('ODS.TAB_GEOSEGMENTO.*', \DB::raw('COUNT(up.id) as ubigeos_count'))
            ->groupBy('ODS.TAB_GEOSEGMENTO.idGeosegmento', 'ODS.TAB_GEOSEGMENTO.geosegmento', 'ODS.TAB_GEOSEGMENTO.lugar', 'ODS.TAB_GEOSEGMENTO.idEstado');

        // Búsqueda global
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ODS.TAB_GEOSEGMENTO.geosegmento', 'like', "%{$search}%")
                  ->orWhere('ODS.TAB_GEOSEGMENTO.lugar', 'like', "%{$search}%");
            });
        }

        // Filtro por ubigeos
        if ($request->has('filter') && $request->filter != '') {
            if ($request->filter == 'sin_ubigeos') {
                $query->having('ubigeos_count', '=', 0);
            } elseif ($request->filter == 'con_ubigeos') {
                $query->having('ubigeos_count', '>', 0);
            }
        }

        $geosegmentos = $query->orderBy('geosegmento')->paginate(15)->appends($request->all());

        return view('geosegmentos.index', compact('geosegmentos', 'ciclos', 'cicloSeleccionado'));
    }

    /**
     * Obtiene un geosegmento por su ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $geosegmento = $this->geosegmentoService->getGeosegmentoById($id);

        if (!$geosegmento) {
            return response()->json([
                'success' => false,
                'message' => 'Geosegmento no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $geosegmento
        ]);
    }

    /**
     * Obtiene la lista de ubigeos de un geosegmento en el ciclo actual.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUbigeosList(int $id): JsonResponse
    {
        $geosegmento = $this->geosegmentoService->getGeosegmentoById($id);

        if (!$geosegmento) {
            return response()->json([
                'success' => false,
                'message' => 'Geosegmento no encontrado.'
            ], 404);
        }

        // Obtener el ciclo abierto actual (basado en fechas)
        $cicloAbierto = \DB::table('ODS.TAB_CICLO')
            ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
            ->first();

        if (!$cicloAbierto) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un ciclo abierto actualmente.',
                'ubigeos' => []
            ], 400);
        }

        // Obtener el periodo-ciclo actual
        $periodoCiclo = \DB::table('ODS.TAB_PERIODO_CICLO')
            ->where('idCiclo', $cicloAbierto->idCiclo)
            ->where('idEstado', 1)
            ->first();

        if (!$periodoCiclo) {
            return response()->json([
                'success' => true,
                'geosegmento' => $geosegmento->geosegmento,
                'ubigeos' => []
            ]);
        }

        // Consultar desde TAB_UBIGEO_PERIODO con JOIN a TAB_UBIGEO
        $ubigeos = \DB::table('ODS.TAB_UBIGEO_PERIODO as up')
            ->join('ODS.TAB_UBIGEO as u', 'up.idUbigeo', '=', 'u.idUbigeo')
            ->where('up.idGeosegmento', $id)
            ->where('up.idPeriodoCiclo', $periodoCiclo->idPeriodoCiclo)
            ->select('u.idUbigeo', 'u.ubigeo', 'u.departamento', 'u.provincia', 'u.distrito')
            ->orderBy('u.departamento')
            ->orderBy('u.provincia')
            ->orderBy('u.distrito')
            ->get();

        return response()->json([
            'success' => true,
            'geosegmento' => $geosegmento->geosegmento,
            'ubigeos' => $ubigeos
        ]);
    }

    /**
     * Asigna ubigeos a un geosegmento en el periodo-ciclo actual (UPDATE).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignUbigeos(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'ubigeos' => 'required|array',
            'ubigeos.*' => 'required|integer',
        ]);

        try {
            $geosegmento = $this->geosegmentoService->getGeosegmentoById($id);

            if (!$geosegmento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geosegmento no encontrado.'
                ], 404);
            }

            // Obtener el ciclo abierto actual (basado en fechas)
            $cicloAbierto = \App\Models\Ciclo::with('estado')
                ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
                ->first();

            if (!$cicloAbierto) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay un ciclo abierto actualmente.'
                ], 400);
            }

            // Verificar si el ciclo está cerrado por estado
            if ($cicloAbierto->estado && $cicloAbierto->estado->estado === 'Cerrado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden asignar ubigeos en un ciclo cerrado.'
                ], 403);
            }

            // Obtener el periodo-ciclo actual del ciclo abierto
            $periodoCiclo = \DB::table('ODS.TAB_PERIODO_CICLO')
                ->where('idCiclo', $cicloAbierto->idCiclo)
                ->where('idEstado', 1)
                ->first();

            if (!$periodoCiclo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un periodo-ciclo activo para el ciclo abierto.'
                ], 400);
            }

            $idPeriodoCiclo = $periodoCiclo->idPeriodoCiclo;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($validated['ubigeos'] as $ubigeoId) {
                // Verificar que el ubigeo existe
                $ubigeoExiste = \DB::table('ODS.TAB_UBIGEO')
                    ->where('idUbigeo', $ubigeoId)
                    ->exists();

                if (!$ubigeoExiste) {
                    continue;
                }

                // Verificar si ya está asignado a este geosegmento en el periodo-ciclo actual
                $registroActual = \DB::table('ODS.TAB_UBIGEO_PERIODO')
                    ->where('idUbigeo', $ubigeoId)
                    ->where('idPeriodoCiclo', $idPeriodoCiclo)
                    ->first();

                if ($registroActual) {
                    // Si ya está asignado a este geosegmento, skip
                    if ($registroActual->idGeosegmento == $id) {
                        $skippedCount++;
                        continue;
                    }

                    // UPDATE: Reasignar el ubigeo al nuevo geosegmento
                    \DB::table('ODS.TAB_UBIGEO_PERIODO')
                        ->where('idUbigeo', $ubigeoId)
                        ->where('idPeriodoCiclo', $idPeriodoCiclo)
                        ->update(['idGeosegmento' => $id]);

                    $updatedCount++;
                } else {
                    // Si no existe el registro en este periodo-ciclo, hacer INSERT
                    // (esto solo debería pasar si el ciclo se acaba de crear y aún no se copió la data)
                    \DB::table('ODS.TAB_UBIGEO_PERIODO')->insert([
                        'idUbigeo' => $ubigeoId,
                        'idGeosegmento' => $id,
                        'idPeriodoCiclo' => $idPeriodoCiclo
                    ]);

                    $updatedCount++;
                }
            }

            $message = [];
            if ($updatedCount > 0) {
                $message[] = "{$updatedCount} ubigeo(s) asignado(s)";
            }
            if ($skippedCount > 0) {
                $message[] = "{$skippedCount} ya estaban asignados";
            }

            return response()->json([
                'success' => true,
                'message' => implode(', ', $message) . ' al ciclo ' . $cicloAbierto->ciclo . '.',
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'ciclo' => $cicloAbierto->ciclo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar ubigeos: ' . $e->getMessage()
            ], 500);
        }
    }
}
