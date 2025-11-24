<?php

namespace App\Http\Controllers;

use App\Services\ZonaService;
use App\Services\GeosegmentoService;
use App\Services\CicloService;
use App\Services\EmpleadoService;
use App\Services\EstadoService;
use App\Services\UbigeoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ZonaController extends Controller
{
    protected ZonaService $zonaService;
    protected GeosegmentoService $geosegmentoService;
    protected CicloService $cicloService;
    protected EmpleadoService $empleadoService;
    protected EstadoService $estadoService;
    protected UbigeoService $ubigeoService;

    public function __construct(
        ZonaService $zonaService,
        GeosegmentoService $geosegmentoService,
        CicloService $cicloService,
        EmpleadoService $empleadoService,
        EstadoService $estadoService,
        UbigeoService $ubigeoService
    ) {
        $this->zonaService = $zonaService;
        $this->geosegmentoService = $geosegmentoService;
        $this->cicloService = $cicloService;
        $this->empleadoService = $empleadoService;
        $this->estadoService = $estadoService;
        $this->ubigeoService = $ubigeoService;
    }

    /**
     * Muestra la vista principal de zonas.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Obtener todos los ciclos con la relación estado cargada
        $ciclos = \App\Models\Ciclo::with('estado')->get();
        
        // Si hay un ciclo seleccionado, obtener datos filtrados por ese ciclo
        $cicloSeleccionado = $request->get('ciclo');
        
        // Si no hay ciclo seleccionado, buscar el ciclo activo más reciente
        if (!$cicloSeleccionado) {
            $cicloActivo = $ciclos->filter(function ($ciclo) {
                $esCerrado = false;
                
                // Verificar por fecha de fin
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                // Verificar por estado
                if (!$esCerrado && $ciclo->estado) {
                    $esCerrado = $ciclo->estado->estado === 'Cerrado';
                }
                
                return !$esCerrado; // Retornar solo ciclos activos
            })->sortByDesc('idCiclo')->first(); // Obtener el más reciente
            
            // Si hay un ciclo activo, seleccionarlo por defecto
            if ($cicloActivo) {
                $cicloSeleccionado = $cicloActivo->idCiclo;
            }
        }
        
        // Query base: SIEMPRE traer todas las zonas
        $query = \App\Models\Zona::with(['estado']);
        
        // Si hay ciclo seleccionado, cargar relaciones filtradas por ciclo
        if ($cicloSeleccionado) {
            $query->with([
                'zonasEmpleados' => function ($q) use ($cicloSeleccionado) {
                    $q->where('idCiclo', $cicloSeleccionado)
                      ->where('idEstado', 1)
                      ->with('empleado');
                },
                'zonasGeosegmentos' => function ($q) use ($cicloSeleccionado) {
                    $q->where('idCiclo', $cicloSeleccionado)
                      ->where('idEstado', 1)
                      ->with('geosegmento');
                }
            ]);
        } else {
            // Sin ciclo seleccionado, cargar todas las relaciones activas
            $query->with([
                'zonasEmpleados' => function ($q) {
                    $q->where('idEstado', 1)->with('empleado');
                },
                'zonasGeosegmentos' => function ($q) {
                    $q->where('idEstado', 1)->with('geosegmento');
                }
            ]);
        }
        
        // Aplicar búsqueda global si existe
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('zona', 'like', "%{$search}%");
        }
        
        // Aplicar filtro de línea si existe
        if ($request->filled('linea') && $cicloSeleccionado) {
            $idLinea = $request->linea;
            
            // Obtener IDs de zonas que tienen representantes de la línea seleccionada
            $zonasConLinea = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_PRODUCTO as p', 'fv.idProducto', '=', 'p.idProducto')
                ->join('ODS.TAB_FRANQLINEA as fl', 'p.idFranqLinea', '=', 'fl.idFranqLinea')
                ->join('ODS.TAB_ZONAEMP as ze', 'fv.idZonaEmp', '=', 'ze.idZonaEmp')
                ->where('fl.idLinea', $idLinea)
                ->where('fv.idCiclo', $cicloSeleccionado)
                ->where('fv.idEstado', 1)
                ->where('ze.idEstado', 1)
                ->distinct()
                ->pluck('ze.idZona');
            
            if ($zonasConLinea->isNotEmpty()) {
                $query->whereIn('idZona', $zonasConLinea);
            } else {
                // Si no hay zonas con esa línea, forzar resultado vacío
                $query->whereRaw('1 = 0');
            }
        }
        
        // Aplicar filtro de empleado (supervisor o representante) si existe
        if ($request->filled('empleado') && $cicloSeleccionado) {
            $empleadoSearch = $request->empleado;
            
            // Buscar en supervisores (TAB_ZONAEMP)
            $zonasConSupervisor = \DB::table('ODS.TAB_ZONAEMP as ze')
                ->join('ODS.TAB_EMPLEADO as e', 'ze.idEmpleado', '=', 'e.idEmpleado')
                ->where('ze.idCiclo', $cicloSeleccionado)
                ->where('ze.idEstado', 1)
                ->where(function($q) use ($empleadoSearch) {
                    $q->where('e.nombre', 'like', "%{$empleadoSearch}%")
                      ->orWhere('e.apeNombre', 'like', "%{$empleadoSearch}%")
                      ->orWhereRaw("CONCAT(e.nombre, ' ', e.apeNombre) like ?", ["%{$empleadoSearch}%"]);
                })
                ->distinct()
                ->pluck('ze.idZona');
            
            // Buscar en representantes (TAB_FUERZAVENTA)
            $zonasConRepresentante = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_EMPLEADO as e', 'fv.idEmpleado', '=', 'e.idEmpleado')
                ->join('ODS.TAB_ZONAEMP as ze', 'fv.idZonaEmp', '=', 'ze.idZonaEmp')
                ->where('fv.idCiclo', $cicloSeleccionado)
                ->where('fv.idEstado', 1)
                ->where('ze.idEstado', 1)
                ->where(function($q) use ($empleadoSearch) {
                    $q->where('e.nombre', 'like', "%{$empleadoSearch}%")
                      ->orWhere('e.apeNombre', 'like', "%{$empleadoSearch}%")
                      ->orWhereRaw("CONCAT(e.nombre, ' ', e.apeNombre) like ?", ["%{$empleadoSearch}%"]);
                })
                ->distinct()
                ->pluck('ze.idZona');
            
            // Combinar ambos resultados
            $zonasConEmpleado = $zonasConSupervisor->merge($zonasConRepresentante)->unique();
            
            if ($zonasConEmpleado->isNotEmpty()) {
                $query->whereIn('idZona', $zonasConEmpleado);
            } else {
                // Si no hay zonas con ese empleado, forzar resultado vacío
                $query->whereRaw('1 = 0');
            }
        }
        
        // Obtener zonas con paginación y mantener parámetros
        $zonas = $query->orderBy('idZona', 'desc')
            ->paginate(10)
            ->appends($request->all());
        
        // Obtener empleados con ausencias activas para este ciclo
        $empleadosConAusencia = collect();
        if ($cicloSeleccionado) {
            $empleadosConAusencia = \DB::table('ODS.TAB_AUSENCIA as a')
                ->join('ODS.TAB_PERIODO_CICLO as pc', 'a.idPeriodoCiclo', '=', 'pc.idPeriodoCiclo')
                ->where('pc.idCiclo', $cicloSeleccionado)
                ->where('a.idEstado', 1)
                ->pluck('a.idEmpleado');
        }
        
        // Agregar el conteo de ubigeos y empleados manualmente para cada zona
        foreach ($zonas as $zona) {
            // Primero obtener los IDs de geosegmentos activos para esta zona y ciclo
            $geosegmentosActivos = \DB::table('ODS.TAB_ZONAGEO')
                ->where('idZona', $zona->idZona)
                ->where('idEstado', 1);
            
            if ($cicloSeleccionado) {
                $geosegmentosActivos->where('idCiclo', $cicloSeleccionado);
            }
            
            $idsGeosegmentos = $geosegmentosActivos->pluck('idGeosegmento')->toArray();
            
            // Contar los ubigeos de esos geosegmentos
            if (count($idsGeosegmentos) > 0) {
                $zona->ubigeos_count = \DB::table('ODS.TAB_UBIGEO')
                    ->whereIn('idGeosegmento', $idsGeosegmentos)
                    ->count();
            } else {
                $zona->ubigeos_count = 0;
            }
            
            // Contar supervisores (de TAB_ZONAEMP)
            $zona->supervisores_count = $zona->zonasEmpleados->count();
            
            // Contar representantes (de TAB_FUERZAVENTA) excluyendo los que tienen ausencia
            $representantesCount = 0;
            foreach ($zona->zonasEmpleados as $zonaEmp) {
                $repsQuery = \DB::table('ODS.TAB_FUERZAVENTA')
                    ->where('idZonaEmp', $zonaEmp->idZonaEmp)
                    ->where('idEstado', 1);
                
                if ($cicloSeleccionado) {
                    $repsQuery->where('idCiclo', $cicloSeleccionado);
                }
                
                if ($empleadosConAusencia->isNotEmpty()) {
                    $repsQuery->whereNotIn('idEmpleado', $empleadosConAusencia);
                }
                
                $representantesCount += $repsQuery->distinct('idEmpleado')->count('idEmpleado');
            }
            $zona->representantes_count = $representantesCount;
        }
        
        $geosegmentos = $this->geosegmentoService->getAllGeosegmentos();
        $empleados = $this->empleadoService->getAllEmpleados();
        $estados = $this->estadoService->getAllEstados();
        $ubigeos = $this->ubigeoService->getAllUbigeos();

        return view('zonas.index', compact('zonas', 'geosegmentos', 'ciclos', 'empleados', 'estados', 'ubigeos', 'cicloSeleccionado'));
    }

    /**
     * Obtiene una zona por su ID con sus asignaciones activas.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $zona = \App\Models\Zona::with([
            'estado',
            'zonasEmpleados' => function ($q) {
                $q->where('idEstado', 1);
            },
            'zonasGeosegmentos' => function ($q) {
                $q->where('idEstado', 1);
            }
        ])->find($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $zona
        ]);
    }

    /**
     * Crea una nueva zona.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zona' => 'required|string|max:100',
            'idEstado' => 'sometimes|integer',
            'geosegmentos' => 'array',
            'geosegmentos.*.idGeosegmento' => 'nullable|integer',
            'geosegmentos.*.idCiclo' => 'required|integer',
            'geosegmentos.*.nuevoGeosegmento' => 'nullable|string|max:100',
            'geosegmentos.*.nuevoLugar' => 'nullable|string|max:100',
        ]);

        $result = $this->zonaService->crearZona($validated);

        // Registrar en el historial si fue exitoso
        if ($result['success'] && isset($result['data'])) {
            $zona = $result['data'];
            
            // HISTORIAL DESACTIVADO TEMPORALMENTE
            /*try {
                \App\Models\Historial::create([
                    'idCiclo' => getActiveCicloId(),
                    'entidad' => 'Zona',
                    'idEntidad' => $zona->idZona,
                    'accion' => 'Crear',
                    'descripcion' => sprintf('Se creó la zona "%s"', $zona->zona),
                    'datosNuevos' => [
                        'zona' => $zona->zona,
                        'idEstado' => $zona->idEstado
                    ],
                    'usuario' => session('usuario.usuario') ?? session('azure_user.name') ?? 'Sistema',
                    'fechaHora' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::debug('No se pudo registrar en historial: ' . $e->getMessage());
            }*/
        }

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Actualiza una zona existente.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Obtener datos anteriores
        $zonaAnterior = \App\Models\Zona::find($id);
        $datosAnteriores = $zonaAnterior ? [
            'zona' => $zonaAnterior->zona,
            'idEstado' => $zonaAnterior->idEstado
        ] : null;

        $validated = $request->validate([
            'zona' => 'sometimes|required|string|max:100|unique:ODS.TAB_ZONA,zona,' . $id . ',idZona',
            'idEstado' => 'sometimes|required|integer',
        ]);

        $result = $this->zonaService->actualizarZona($id, $validated);

        // Registrar en el historial si fue exitoso
        if ($result['success'] && isset($result['data'])) {
            $zona = $result['data'];
            
            // HISTORIAL DESACTIVADO TEMPORALMENTE
            /*try {
                \App\Models\Historial::create([
                    'idCiclo' => getActiveCicloId(),
                    'entidad' => 'Zona',
                    'idEntidad' => $zona->idZona,
                    'accion' => 'Actualizar',
                    'descripcion' => sprintf('Se actualizó la zona "%s"', $zona->zona),
                    'datosAnteriores' => $datosAnteriores,
                    'datosNuevos' => [
                        'zona' => $zona->zona,
                        'idEstado' => $zona->idEstado
                    ],
                    'usuario' => session('usuario.usuario') ?? session('azure_user.name') ?? 'Sistema',
                    'fechaHora' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::debug('No se pudo registrar en historial: ' . $e->getMessage());
            }*/
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Desactiva una zona (cambia estado a 0).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Obtener datos de la zona antes de desactivar
        $zona = \App\Models\Zona::find($id);
        
        // Actualizar la zona con estado inactivo (idEstado = 0)
        $result = $this->zonaService->actualizarZona($id, [
            'idEstado' => 0
        ]);

        if ($result['success']) {
            $result['message'] = 'Zona desactivada exitosamente.';
            
            // Registrar en el historial
            if ($zona) {
                // HISTORIAL DESACTIVADO TEMPORALMENTE
                /*try {
                    \App\Models\Historial::create([
                        'idCiclo' => getActiveCicloId(),
                        'entidad' => 'Zona',
                        'idEntidad' => $zona->idZona,
                        'accion' => 'Desactivar',
                        'descripcion' => sprintf('Se desactivó la zona "%s"', $zona->zona),
                        'datosAnteriores' => ['idEstado' => $zona->idEstado],
                        'datosNuevos' => ['idEstado' => 0],
                        'usuario' => session('usuario.usuario') ?? session('azure_user.name') ?? 'Sistema',
                        'fechaHora' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Log::debug('No se pudo registrar en historial: ' . $e->getMessage());
                }*/
            }
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Obtiene los empleados asignados a una zona.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getEmpleados(Request $request, int $id): JsonResponse
    {
        $cicloId = $request->get('ciclo');
        
        // Obtener supervisores (de TAB_ZONAEMP)
        $zona = \App\Models\Zona::with([
            'zonasEmpleados' => function ($q) use ($cicloId) {
                $q->where('idEstado', 1);
                if ($cicloId) {
                    $q->where('idCiclo', $cicloId);
                }
            },
            'zonasEmpleados.empleado.cargo',
            'zonasEmpleados.ciclo',
            'zonasEmpleados.estado'
        ])->find($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        // Obtener representantes médicos (de TAB_FUERZAVENTA)
        $representantes = collect();
        
        foreach ($zona->zonasEmpleados as $zonaEmp) {
            // Buscar representantes médicos asociados a este supervisor en TAB_FUERZAVENTA
            $repsQuery = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_EMPLEADO as e', 'fv.idEmpleado', '=', 'e.idEmpleado')
                ->leftJoin('ODS.TAB_CARGO as c', 'e.idCargo', '=', 'c.idCargo')
                ->leftJoin('ODS.TAB_PRODUCTO as p', 'fv.idProducto', '=', 'p.idProducto')
                ->leftJoin('ODS.TAB_FRANQLINEA as fl', 'p.idFranqLinea', '=', 'fl.idFranqLinea')
                ->leftJoin('ODS.TAB_LINEA as l', 'fl.idLinea', '=', 'l.idLinea')
                ->where('fv.idZonaEmp', $zonaEmp->idZonaEmp)
                ->where('fv.idEstado', 1);
            
            if ($cicloId) {
                $repsQuery->where('fv.idCiclo', $cicloId);
            }
            
            $reps = $repsQuery->select(
                \DB::raw('MIN(fv.idFuerza) as idFuerza'),
                'fv.idEmpleado',
                'e.nombre',
                'e.apeNombre',
                'e.correo',
                'c.cargo',
                'fv.idZonaEmp',
                \DB::raw('MIN(l.linea) as linea')
            )
            ->groupBy('fv.idEmpleado', 'e.nombre', 'e.apeNombre', 'e.correo', 'c.cargo', 'fv.idZonaEmp')
            ->get();
            
            foreach ($reps as $rep) {
                $representantes->push([
                    'idFuerza' => $rep->idFuerza,
                    'idEmpleado' => $rep->idEmpleado,
                    'nombre' => trim(($rep->nombre ?? '') . ' ' . ($rep->apeNombre ?? '')),
                    'correo' => $rep->correo,
                    'cargo' => $rep->cargo ?? 'Representante Médico',
                    'tipo' => 'representante',
                    'idZonaEmp' => $rep->idZonaEmp,
                    'linea' => $rep->linea ?? 'Sin línea'
                ]);
            }
        }

        // Formatear supervisores
        $supervisores = $zona->zonasEmpleados->map(function ($zonaEmp) {
            return [
                'idZonaEmp' => $zonaEmp->idZonaEmp,
                'idEmpleado' => $zonaEmp->idEmpleado,
                'nombre' => $zonaEmp->empleado->nombre ?? 'N/A',
                'correo' => $zonaEmp->empleado->correo ?? 'N/A',
                'cargo' => $zonaEmp->empleado->cargo->cargo ?? 'Supervisor',
                'tipo' => 'supervisor',
                'ciclo' => $zonaEmp->ciclo->ciclo ?? 'N/A',
                'estado' => $zonaEmp->estado->estado ?? 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'supervisores' => $supervisores,
                'representantes' => $representantes
            ]
        ]);
    }

    /**
     * Obtiene los geosegmentos asignados a una zona (activos e inactivos).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getGeosegmentos(Request $request, int $id): JsonResponse
    {
        $cicloId = $request->get('ciclo');
        
        $zona = \App\Models\Zona::with([
            'zonasGeosegmentos' => function ($q) use ($cicloId) {
                // Traer todos los geosegmentos (activos e inactivos)
                if ($cicloId) {
                    $q->where('idCiclo', $cicloId);
                }
            },
            'zonasGeosegmentos.geosegmento',
            'zonasGeosegmentos.ciclo',
            'zonasGeosegmentos.estado'
        ])->find($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        // Separar activos e inactivos
        $activos = $zona->zonasGeosegmentos->where('idEstado', 1);
        $inactivos = $zona->zonasGeosegmentos->where('idEstado', 0);

        return response()->json([
            'success' => true,
            'data' => [
                'activos' => $activos->values(),
                'inactivos' => $inactivos->values()
            ]
        ]);
    }

    /**
     * Obtiene los ubigeos relacionados con los geosegmentos de una zona.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getUbigeos(Request $request, int $id): JsonResponse
    {
        $cicloId = $request->get('ciclo');
        
        $zona = \App\Models\Zona::with([
            'zonasGeosegmentos' => function ($q) use ($cicloId) {
                $q->where('idEstado', 1);
                if ($cicloId) {
                    $q->where('idCiclo', $cicloId);
                }
            },
            'zonasGeosegmentos.geosegmento.ubigeos.geosegmento'
        ])->find($id);

        if (!$zona) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ], 404);
        }

        // Recopilar todos los ubigeos de los geosegmentos asignados
        $ubigeos = collect();
        foreach ($zona->zonasGeosegmentos as $zonaGeo) {
            if ($zonaGeo->geosegmento && $zonaGeo->geosegmento->ubigeos) {
                $ubigeos = $ubigeos->merge($zonaGeo->geosegmento->ubigeos);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $ubigeos->unique('idUbigeo')->values()
        ]);
    }

    /**
     * Desactiva un geosegmento de una zona (cambia estado a 0 en ZonaGeo).
     *
     * @param int $id El ID de la relación ZonaGeo
     * @return JsonResponse
     */
    public function deactivateGeosegmentFromZone(int $id): JsonResponse
    {
        try {
            $zonaGeo = \App\Models\ZonaGeo::with(['ciclo.estado', 'zona', 'geosegmento'])->find($id);

            if (!$zonaGeo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relación no encontrada.'
                ], 404);
            }

            // Verificar si el ciclo está cerrado (por fecha o por estado)
            if ($zonaGeo->ciclo) {
                $ciclo = $zonaGeo->ciclo;
                $esCerrado = false;
                
                // Verificar por fecha de fin
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                // Fallback: verificar por estado si no hay fecha usando la RELACIÓN, no el accesor
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            $zonaGeo->idEstado = 0;
            $zonaGeo->save();

            // HISTORIAL DESACTIVADO TEMPORALMENTE
            /*\App\Models\Historial::create([
                'idCiclo' => $zonaGeo->idCiclo,
                'entidad' => 'ZonaGeosegmento',
                'idEntidad' => $zonaGeo->idZonaGeo,
                'accion' => 'Desasignar',
                'descripcion' => sprintf(
                    'Se desasignó el geosegmento "%s" de la zona "%s"',
                    $zonaGeo->geosegmento->geosegmento ?? 'N/A',
                    $zonaGeo->zona->zona ?? 'N/A'
                ),
                'datosAnteriores' => ['idEstado' => 1],
                'datosNuevos' => ['idEstado' => 0],
                'usuario' => session('azure_user')['name'] ?? 'Sistema',
                'fechaHora' => now(),
            ]);*/

            return response()->json([
                'success' => true,
                'message' => 'Geosegmento desasignado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desasignar el geosegmento: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Agrega un empleado a una zona.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addEmpleadoToZone(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'idEmpleado' => 'required|integer',
                'idCiclo' => 'required|integer'
            ]);

            // Verificar si la zona existe
            $zona = \App\Models\Zona::find($id);
            if (!$zona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona no encontrada.'
                ], 404);
            }

            // Verificar si el ciclo está cerrado
            $ciclo = \App\Models\Ciclo::find($request->idCiclo);
            if ($ciclo) {
                $esCerrado = false;
                
                // Verificar por fecha de fin
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                // Fallback: verificar por estado si no hay fecha
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            // Verificar si ya existe la relación
            $existingRelation = \App\Models\ZonaEmp::where('idZona', $id)
                ->where('idEmpleado', $request->idEmpleado)
                ->where('idCiclo', $request->idCiclo)
                ->first();

            if ($existingRelation) {
                if ($existingRelation->idEstado == 0) {
                    // Si existe pero está inactivo, reactivarlo
                    $existingRelation->idEstado = 1;
                    $existingRelation->save();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Empleado reactivado exitosamente.'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este empleado ya está asignado a la zona.'
                    ], 400);
                }
            }

            // Crear nueva relación
            $zonaEmp = \App\Models\ZonaEmp::create([
                'idZona' => $id,
                'idEmpleado' => $request->idEmpleado,
                'idCiclo' => $request->idCiclo,
                'idEstado' => 1
            ]);

            // Cargar relaciones para el historial
            $zonaEmp->load(['zona', 'empleado']);

            // HISTORIAL DESACTIVADO TEMPORALMENTE
            /*\App\Models\Historial::create([
                'idCiclo' => $request->idCiclo,
                'entidad' => 'ZonaEmpleado',
                'idEntidad' => $zonaEmp->idZonaEmp,
                'accion' => 'Asignar',
                'descripcion' => sprintf(
                    'Se asignó el empleado "%s" a la zona "%s"',
                    $zonaEmp->empleado->nombre ?? 'N/A',
                    $zonaEmp->zona->zona ?? 'N/A'
                ),
                'datosNuevos' => [
                    'idZona' => $id,
                    'idEmpleado' => $request->idEmpleado,
                    'idCiclo' => $request->idCiclo,
                    'idEstado' => 1
                ],
                'usuario' => session('azure_user')['name'] ?? 'Sistema',
                'fechaHora' => now(),
            ]);*/

            return response()->json([
                'success' => true,
                'message' => 'Empleado agregado exitosamente.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el empleado: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Agrega un representante médico a una zona (inserta en TAB_FUERZAVENTA).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addRepresentanteToZone(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'idEmpleado' => 'required|integer',
                'idZonaEmp' => 'required|integer',
                'idCiclo' => 'required|integer',
                'idFranqLinea' => 'required|integer'
            ]);

            // Verificar si la zona existe
            $zona = \App\Models\Zona::find($id);
            if (!$zona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona no encontrada.'
                ], 404);
            }

            // Verificar si el ciclo está cerrado
            $ciclo = \App\Models\Ciclo::find($request->idCiclo);
            if ($ciclo) {
                $esCerrado = false;
                
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            // Verificar si ya existe la relación
            $existingRelation = \DB::table('ODS.TAB_FUERZAVENTA')
                ->where('idZonaEmp', $request->idZonaEmp)
                ->where('idEmpleado', $request->idEmpleado)
                ->where('idCiclo', $request->idCiclo)
                ->first();

            if ($existingRelation) {
                if ($existingRelation->idEstado == 0) {
                    // Si existe pero está inactivo, reactivarlo
                    \DB::table('ODS.TAB_FUERZAVENTA')
                        ->where('idFuerza', $existingRelation->idFuerza)
                        ->update(['idEstado' => 1]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Representante reactivado exitosamente.'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este representante ya está asignado a esta zona.'
                    ], 400);
                }
            }

            // Calcular periodo de comisión basado en la fecha de inicio del ciclo
            $fechaInicioCiclo = \Carbon\Carbon::parse($ciclo->fechaInicio);
            $periodoComision = $fechaInicioCiclo->format('Ym');

            // Obtener todos los productos de la franqlinea seleccionada para este ciclo
            $productos = \DB::table('ODS.TAB_PRODUCTO')
                ->where('idFranqLinea', $request->idFranqLinea)
                ->where('idCiclo', $request->idCiclo)
                ->where('idEstado', 1)
                ->pluck('idProducto');

            if ($productos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron productos activos para esta franquicia/línea en el ciclo seleccionado.'
                ], 400);
            }

            // Crear un registro en TAB_FUERZAVENTA por cada producto
            $insertados = 0;
            foreach ($productos as $idProducto) {
                // Verificar si ya existe
                $existe = \DB::table('ODS.TAB_FUERZAVENTA')
                    ->where('idZonaEmp', $request->idZonaEmp)
                    ->where('idEmpleado', $request->idEmpleado)
                    ->where('idProducto', $idProducto)
                    ->where('idCiclo', $request->idCiclo)
                    ->exists();

                if (!$existe) {
                    \DB::table('ODS.TAB_FUERZAVENTA')->insert([
                        'idCiclo' => $request->idCiclo,
                        'idZonaEmp' => $request->idZonaEmp,
                        'idProducto' => $idProducto,
                        'idEmpleado' => $request->idEmpleado,
                        'fechaModificacion' => now(),
                        'fechaCierre' => null,
                        'idEstado' => 1,
                        'periodoComision' => $periodoComision
                    ]);
                    $insertados++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Representante médico agregado exitosamente con {$insertados} producto(s).",
                'productosInsertados' => $insertados
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el representante: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Agrega un geosegmento a una zona.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addGeosegmentToZone(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'idGeosegmento' => 'required|integer',
                'idCiclo' => 'required|integer'
            ]);

            // Verificar si la zona existe
            $zona = \App\Models\Zona::find($id);
            if (!$zona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona no encontrada.'
                ], 404);
            }

            // Verificar si el ciclo está cerrado
            $ciclo = \App\Models\Ciclo::find($request->idCiclo);
            if ($ciclo) {
                $esCerrado = false;
                
                // Verificar por fecha de fin
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                // Fallback: verificar por estado si no hay fecha
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            // Verificar si ya existe la relación
            $existingRelation = \App\Models\ZonaGeo::where('idZona', $id)
                ->where('idGeosegmento', $request->idGeosegmento)
                ->where('idCiclo', $request->idCiclo)
                ->first();

            if ($existingRelation) {
                if ($existingRelation->idEstado == 0) {
                    // Si existe pero está inactivo, reactivarlo
                    $existingRelation->idEstado = 1;
                    $existingRelation->save();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Geosegmento reactivado exitosamente.'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este geosegmento ya está asignado a la zona.'
                    ], 400);
                }
            }

            // Crear nueva relación
            $zonaGeo = \App\Models\ZonaGeo::create([
                'idZona' => $id,
                'idGeosegmento' => $request->idGeosegmento,
                'idCiclo' => $request->idCiclo,
                'idEstado' => 1
            ]);

            // Cargar relaciones para el historial
            $zonaGeo->load(['zona', 'geosegmento']);

            // HISTORIAL DESACTIVADO TEMPORALMENTE
            /*\App\Models\Historial::create([
                'idCiclo' => $request->idCiclo,
                'entidad' => 'ZonaGeosegmento',
                'idEntidad' => $zonaGeo->idZonaGeo,
                'accion' => 'Asignar',
                'descripcion' => sprintf(
                    'Se asignó el geosegmento "%s" a la zona "%s"',
                    $zonaGeo->geosegmento->geosegmento ?? 'N/A',
                    $zonaGeo->zona->zona ?? 'N/A'
                ),
                'datosNuevos' => [
                    'idZona' => $id,
                    'idGeosegmento' => $request->idGeosegmento,
                    'idCiclo' => $request->idCiclo,
                    'idEstado' => 1
                ],
                'usuario' => session('azure_user')['name'] ?? 'Sistema',
                'fechaHora' => now(),
            ]);*/

            return response()->json([
                'success' => true,
                'message' => 'Geosegmento agregado exitosamente.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el geosegmento: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Desactiva un empleado de una zona (cambia estado a 0 en ZonaEmp).
     *
     * @param int $id El ID de la relación ZonaEmp
     * @return JsonResponse
     */
    public function deactivateEmployeeFromZone(int $id): JsonResponse
    {
        try {
            $zonaEmp = \App\Models\ZonaEmp::with(['ciclo.estado', 'zona', 'empleado'])->find($id);

            if (!$zonaEmp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relación no encontrada.'
                ], 404);
            }

            // Verificar si el ciclo está cerrado (por fecha o por estado)
            if ($zonaEmp->ciclo) {
                $ciclo = $zonaEmp->ciclo;
                $esCerrado = false;
                
                // Verificar por fecha de fin
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                // Fallback: verificar por estado si no hay fecha usando la RELACIÓN, no el accesor
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            $zonaEmp->idEstado = 0;
            $zonaEmp->save();

            // HISTORIAL DESACTIVADO TEMPORALMENTE
            /*\App\Models\Historial::create([
                'idCiclo' => $zonaEmp->idCiclo,
                'entidad' => 'ZonaEmpleado',
                'idEntidad' => $zonaEmp->idZonaEmp,
                'accion' => 'Desasignar',
                'descripcion' => sprintf(
                    'Se desasignó el empleado "%s" de la zona "%s"',
                    $zonaEmp->empleado->nombre ?? 'N/A',
                    $zonaEmp->zona->zona ?? 'N/A'
                ),
                'datosAnteriores' => ['idEstado' => 1],
                'datosNuevos' => ['idEstado' => 0],
                'usuario' => session('azure_user')['name'] ?? 'Sistema',
                'fechaHora' => now(),
            ]);*/

            return response()->json([
                'success' => true,
                'message' => 'Empleado desasignado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desasignar el empleado: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reactiva un geosegmento de una zona (cambia estado a 1).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function activateGeosegmentFromZone(int $id): JsonResponse
    {
        try {
            $zonaGeo = \App\Models\ZonaGeo::with(['ciclo.estado', 'zona', 'geosegmento'])->find($id);

            if (!$zonaGeo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relación no encontrada.'
                ], 404);
            }

            // Verificar si el ciclo está cerrado (por fecha o por estado)
            if ($zonaGeo->ciclo) {
                $ciclo = $zonaGeo->ciclo;
                $esCerrado = false;
                
                // Verificar por fecha de fin
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                // Fallback: verificar por estado si no hay fecha usando la RELACIÓN, no el accesor
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            $zonaGeo->idEstado = 1;
            $zonaGeo->save();

            // HISTORIAL DESACTIVADO TEMPORALMENTE
            /*\App\Models\Historial::create([
                'idCiclo' => $zonaGeo->idCiclo,
                'entidad' => 'ZonaGeosegmento',
                'idEntidad' => $zonaGeo->idZonaGeo,
                'accion' => 'Asignar',
                'descripcion' => sprintf(
                    'Se asignó el geosegmento "%s" a la zona "%s"',
                    $zonaGeo->geosegmento->geosegmento ?? 'N/A',
                    $zonaGeo->zona->zona ?? 'N/A'
                ),
                'datosAnteriores' => ['idEstado' => 0],
                'datosNuevos' => ['idEstado' => 1],
                'usuario' => session('azure_user')['name'] ?? 'Sistema',
                'fechaHora' => now(),
            ]);*/

            return response()->json([
                'success' => true,
                'message' => 'Geosegmento reactivado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el geosegmento: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtiene los detalles completos de una zona con empleados y geosegmentos.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function detalles(Request $request, int $id): JsonResponse
    {
        try {
            $cicloId = $request->get('ciclo');
            
            $query = \App\Models\Zona::with(['estado']);
            
            if ($cicloId) {
                $query->with([
                    'zonasEmpleados' => function ($q) use ($cicloId) {
                        $q->where('idCiclo', $cicloId)
                          ->where('idEstado', 1)
                          ->with('empleado');
                    },
                    'zonasGeosegmentos' => function ($q) use ($cicloId) {
                        $q->where('idCiclo', $cicloId)
                          ->where('idEstado', 1)
                          ->with('geosegmento');
                    }
                ]);
            } else {
                $query->with([
                    'zonasEmpleados' => function ($q) {
                        $q->where('idEstado', 1)->with('empleado');
                    },
                    'zonasGeosegmentos' => function ($q) {
                        $q->where('idEstado', 1)->with('geosegmento');
                    }
                ]);
            }
            
            $zona = $query->find($id);

            if (!$zona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona no encontrada.'
                ], 404);
            }

            // Formatear supervisores
            $supervisores = $zona->zonasEmpleados->map(function ($ze) {
                return [
                    'id' => $ze->idZonaEmp,
                    'nombre' => $ze->empleado->nombre ?? 'Sin nombre',
                    'tipo' => 'supervisor',
                    'cargo' => $ze->empleado->cargo->cargo ?? 'Supervisor'
                ];
            });

            // Obtener representantes médicos de TAB_FUERZAVENTA
            $representantes = collect();
            
            \Log::error('DEBUG: Buscando representantes para zona', [
                'idZona' => $id,
                'cicloId' => $cicloId,
                'supervisores_count' => $zona->zonasEmpleados->count()
            ]);
            
            // Obtener empleados con ausencias activas en este ciclo (renuncia y licencia)
            $empleadosConRenuncia = collect();
            $empleadosConLicencia = collect();
            $ausenciasInfo = collect();
            
            if ($cicloId) {
                // Obtener renuncias
                $empleadosConRenuncia = \DB::table('ODS.TAB_AUSENCIA as a')
                    ->join('ODS.TAB_PERIODO_CICLO as pc', 'a.idPeriodoCiclo', '=', 'pc.idPeriodoCiclo')
                    ->join('ODS.TAB_TIPO_AUSENCIA as ta', 'a.idTipoAusencia', '=', 'ta.idTipoAusencia')
                    ->where('pc.idCiclo', $cicloId)
                    ->where('a.idEstado', 1)
                    ->whereRaw('LOWER(ta.tipo) = ?', ['renuncia'])
                    ->pluck('a.idEmpleado');
                
                // Obtener licencias con información completa
                $licencias = \DB::table('ODS.TAB_AUSENCIA as a')
                    ->join('ODS.TAB_PERIODO_CICLO as pc', 'a.idPeriodoCiclo', '=', 'pc.idPeriodoCiclo')
                    ->join('ODS.TAB_TIPO_AUSENCIA as ta', 'a.idTipoAusencia', '=', 'ta.idTipoAusencia')
                    ->where('pc.idCiclo', $cicloId)
                    ->where('a.idEstado', 1)
                    ->whereRaw('LOWER(ta.tipo) = ?', ['licencia'])
                    ->select('a.idEmpleado', 'a.idAusencia', 'a.fechaInicio', 'a.fechaFin', 'a.observacion')
                    ->get();
                
                foreach ($licencias as $licencia) {
                    $empleadosConLicencia->push($licencia->idEmpleado);
                    $ausenciasInfo->put($licencia->idEmpleado, [
                        'idAusencia' => $licencia->idAusencia,
                        'tipo' => 'licencia',
                        'fechaInicio' => $licencia->fechaInicio,
                        'fechaFin' => $licencia->fechaFin,
                        'observacion' => $licencia->observacion
                    ]);
                }
            }
            
            foreach ($zona->zonasEmpleados as $zonaEmp) {
                $repsQuery = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                    ->join('ODS.TAB_EMPLEADO as e', 'fv.idEmpleado', '=', 'e.idEmpleado')
                    ->leftJoin('ODS.TAB_CARGO as c', 'e.idCargo', '=', 'c.idCargo')
                    ->leftJoin('ODS.TAB_PRODUCTO as p', 'fv.idProducto', '=', 'p.idProducto')
                    ->leftJoin('ODS.TAB_FRANQLINEA as fl', 'p.idFranqLinea', '=', 'fl.idFranqLinea')
                    ->leftJoin('ODS.TAB_LINEA as l', 'fl.idLinea', '=', 'l.idLinea')
                    ->where('fv.idZonaEmp', $zonaEmp->idZonaEmp)
                    ->where('fv.idEstado', 1);
                
                if ($cicloId) {
                    $repsQuery->where('fv.idCiclo', $cicloId);
                }
                
                $reps = $repsQuery->select(
                    \DB::raw('MIN(fv.idFuerza) as idFuerza'),
                    'fv.idEmpleado',
                    'e.nombre',
                    'e.apeNombre',
                    'c.cargo',
                    \DB::raw('MIN(l.linea) as linea')
                )
                ->groupBy('fv.idEmpleado', 'e.nombre', 'e.apeNombre', 'c.cargo')
                ->get();
                
                foreach ($reps as $rep) {
                    $esRenuncia = $empleadosConRenuncia->contains($rep->idEmpleado);
                    $esLicencia = $empleadosConLicencia->contains($rep->idEmpleado);
                    $linea = $rep->linea ?? 'Sin línea';
                    
                    $ausenciaInfo = null;
                    if ($esLicencia && $ausenciasInfo->has($rep->idEmpleado)) {
                        $ausenciaInfo = $ausenciasInfo->get($rep->idEmpleado);
                    }
                    
                    $representantes->push([
                        'id' => $rep->idFuerza,
                        'idEmpleado' => $rep->idEmpleado,
                        'nombre' => $esRenuncia ? "VACANTE {$linea}" : trim(($rep->nombre ?? '') . ' ' . ($rep->apeNombre ?? '')),
                        'tipo' => 'representante',
                        'cargo' => $rep->cargo ?? 'Representante Médico',
                        'linea' => $linea,
                        'esVacante' => $esRenuncia,
                        'esLicencia' => $esLicencia,
                        'ausenciaInfo' => $ausenciaInfo
                    ]);
                }
            }

            // Combinar supervisores y representantes
            $empleados = $supervisores->concat($representantes);
            
            \Log::error('DEBUG: Total empleados', [
                'supervisores' => $supervisores->count(),
                'representantes' => $representantes->count(),
                'total' => $empleados->count(),
                'empleados_array' => $empleados->toArray()
            ]);

            // Formatear geosegmentos
            $geosegmentos = $zona->zonasGeosegmentos->map(function ($zg) {
                return [
                    'id' => $zg->idZonaGeo,
                    'geosegmento' => $zg->geosegmento->geosegmento ?? 'Sin nombre'
                ];
            });

            // Contar ubigeos asociados a los geosegmentos de esta zona
            $ubigeosCount = \DB::table('ODS.TAB_UBIGEO')
                ->whereIn('idGeosegmento', function ($query) use ($id, $cicloId) {
                    $query->select('idGeosegmento')
                        ->from('ODS.TAB_ZONAGEO')
                        ->where('idZona', $id)
                        ->where('idEstado', 1);
                    if ($cicloId) {
                        $query->where('idCiclo', $cicloId);
                    }
                })
                ->count();

            return response()->json([
                'success' => true,
                'zona' => [
                    'idZona' => $zona->idZona,
                    'zona' => $zona->zona,
                    'estado' => $zona->estado,
                    'ubigeos_count' => $ubigeosCount,
                    'empleados' => $empleados,
                    'geosegmentos' => $geosegmentos
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina múltiples geosegmentos de una zona de forma masiva.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function bulkRemoveGeosegmentos(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'geosegmentos' => 'required|array',
                'geosegmentos.*' => 'integer'
            ]);

            $removidos = 0;
            foreach ($request->geosegmentos as $idZonaGeo) {
                $zonaGeo = \App\Models\ZonaGeo::find($idZonaGeo);
                if ($zonaGeo && $zonaGeo->idZona == $id) {
                    $zonaGeo->idEstado = 0;
                    $zonaGeo->save();
                    $removidos++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$removidos} geosegmento(s) eliminado(s) exitosamente."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar geosegmentos: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Copia geosegmentos de otras zonas a la zona actual.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function copyGeosegmentosFromZones(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'zonasOrigen' => 'required|array',
                'zonasOrigen.*' => 'integer',
                'idCiclo' => 'required|integer'
            ]);

            $cicloId = $request->idCiclo;

            // PASO 1: Eliminar (desactivar) todos los geosegmentos actuales de la zona destino
            $eliminados = \DB::table('ODS.TAB_ZONAGEO')
                ->where('idZona', $id)
                ->where('idCiclo', $cicloId)
                ->where('idEstado', 1)
                ->update(['idEstado' => 0]);

            // PASO 2: Obtener todos los geosegmentos únicos de las zonas origen
            $geosegmentosUnicos = \DB::table('ODS.TAB_ZONAGEO')
                ->whereIn('idZona', $request->zonasOrigen)
                ->where('idCiclo', $cicloId)
                ->where('idEstado', 1)
                ->distinct()
                ->pluck('idGeosegmento');

            $insertados = 0;
            $reactivados = 0;

            // PASO 3: Insertar o reactivar los geosegmentos de las zonas origen
            foreach ($geosegmentosUnicos as $idGeosegmento) {
                // Verificar si existe un registro previo (aunque esté inactivo)
                $existe = \DB::table('ODS.TAB_ZONAGEO')
                    ->where('idZona', $id)
                    ->where('idGeosegmento', $idGeosegmento)
                    ->where('idCiclo', $cicloId)
                    ->first();

                if ($existe) {
                    // Reactivar el registro existente
                    \DB::table('ODS.TAB_ZONAGEO')
                        ->where('idZonaGeo', $existe->idZonaGeo)
                        ->update(['idEstado' => 1]);
                    $reactivados++;
                } else {
                    // Insertar nuevo registro
                    \DB::table('ODS.TAB_ZONAGEO')->insert([
                        'idZona' => $id,
                        'idGeosegmento' => $idGeosegmento,
                        'idEstado' => 1,
                        'idCiclo' => $cicloId
                    ]);
                    $insertados++;
                }
            }

            $totalCopiados = $insertados + $reactivados;

            return response()->json([
                'success' => true,
                'message' => "Se reemplazaron {$eliminados} geosegmento(s) por {$totalCopiados} nuevo(s)",
                'eliminados' => $eliminados,
                'insertados' => $insertados,
                'reactivados' => $reactivados,
                'total' => $totalCopiados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al copiar geosegmentos: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reactiva un empleado desactivando su ausencia de tipo licencia.
     *
     * @param Request $request
     * @param int $idEmpleado
     * @return JsonResponse
     */
    public function reactivarEmpleadoLicencia(Request $request, int $idEmpleado): JsonResponse
    {
        try {
            $request->validate([
                'idCiclo' => 'required|integer'
            ]);

            // Verificar si el ciclo está cerrado
            $ciclo = \App\Models\Ciclo::find($request->idCiclo);
            if ($ciclo) {
                $esCerrado = false;
                
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            // Obtener el periodo del ciclo
            $periodoCiclo = \DB::table('ODS.TAB_PERIODO_CICLO')
                ->where('idCiclo', $request->idCiclo)
                ->where('idEstado', 1)
                ->orderBy('idPeriodoCiclo', 'desc')
                ->first();

            if (!$periodoCiclo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un periodo activo para este ciclo.'
                ], 404);
            }

            // Buscar la ausencia de tipo licencia activa para este empleado
            $ausencia = \DB::table('ODS.TAB_AUSENCIA as a')
                ->join('ODS.TAB_TIPO_AUSENCIA as ta', 'a.idTipoAusencia', '=', 'ta.idTipoAusencia')
                ->where('a.idEmpleado', $idEmpleado)
                ->where('a.idPeriodoCiclo', $periodoCiclo->idPeriodoCiclo)
                ->where('a.idEstado', 1)
                ->whereRaw('LOWER(ta.tipo) = ?', ['licencia'])
                ->select('a.idAusencia')
                ->first();

            if (!$ausencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una licencia activa para este empleado.'
                ], 404);
            }

            // Desactivar la ausencia (cambiar estado a 0)
            \DB::table('ODS.TAB_AUSENCIA')
                ->where('idAusencia', $ausencia->idAusencia)
                ->update(['idEstado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Empleado reactivado exitosamente. La licencia ha sido finalizada.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el empleado: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Quita un representante médico de una zona y registra su ausencia.
     *
     * @param Request $request
     * @param int $idFuerza
     * @return JsonResponse
     */
    public function removeRepresentanteWithAusencia(Request $request, int $idFuerza): JsonResponse
    {
        try {
            $request->validate([
                'idEmpleado' => 'required|integer',
                'idTipoAusencia' => 'required|integer',
                'idCiclo' => 'required|integer',
                'fechaInicio' => 'required|date',
                'fechaFin' => 'nullable|date',
                'observacion' => 'nullable|string'
            ]);

            // Verificar si el ciclo está cerrado
            $ciclo = \App\Models\Ciclo::find($request->idCiclo);
            if ($ciclo) {
                $esCerrado = false;
                
                if ($ciclo->fechaFin) {
                    $fechaFin = \Carbon\Carbon::parse($ciclo->fechaFin)->startOfDay();
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    $esCerrado = $fechaFin->lt($hoy);
                }
                
                if (!$esCerrado) {
                    $estadoRelacion = $ciclo->relationLoaded('estado') ? $ciclo->getRelation('estado') : $ciclo->estado()->first();
                    if ($estadoRelacion && $estadoRelacion->estado === 'Cerrado') {
                        $esCerrado = true;
                    }
                }
                
                if ($esCerrado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pueden realizar modificaciones en un ciclo cerrado.'
                    ], 403);
                }
            }

            // Obtener el periodo del ciclo
            $periodoCiclo = \DB::table('ODS.TAB_PERIODO_CICLO')
                ->where('idCiclo', $request->idCiclo)
                ->where('idEstado', 1)
                ->orderBy('idPeriodoCiclo', 'desc')
                ->first();

            if (!$periodoCiclo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un periodo activo para este ciclo.'
                ], 404);
            }

            // Iniciar transacción
            \DB::beginTransaction();

            // 1. Registrar la ausencia
            $idAusencia = \DB::table('ODS.TAB_AUSENCIA')->insertGetId([
                'idTipoAusencia' => $request->idTipoAusencia,
                'idEmpleado' => $request->idEmpleado,
                'idPeriodoCiclo' => $periodoCiclo->idPeriodoCiclo,
                'idEstado' => 1,
                'fechaInicio' => $request->fechaInicio,
                'fechaFin' => $request->fechaFin,
                'observacion' => $request->observacion,
                'fechaRegistro' => now()
            ]);

            // 2. NO desactivar en TAB_FUERZAVENTA - mantener activo para futuro reemplazo
            // Los registros permanecen activos para que puedan ser actualizados con el nuevo representante

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Ausencia registrada exitosamente. El representante puede ser reemplazado.",
                'idAusencia' => $idAusencia
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
                'message' => 'Error al remover el representante: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cambia un supervisor de una zona.
     *
     * @param Request $request
     * @param int $idZonaEmp
     * @return JsonResponse
     */
    public function cambiarSupervisor(Request $request, int $idZonaEmp): JsonResponse
    {
        try {
            $request->validate([
                'idEmpleadoAntiguo' => 'required|integer',
                'idEmpleadoNuevo' => 'required|integer',
                'idCiclo' => 'required|integer'
            ]);

            \DB::beginTransaction();

            // 1. Obtener el registro antiguo de ZonaEmp
            $zonaEmpAntiguo = \DB::table('ODS.TAB_ZONAEMP')->where('idZonaEmp', $idZonaEmp)->first();
            
            if (!$zonaEmpAntiguo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado.'
                ], 404);
            }

            // 2. Desactivar el registro antiguo
            \DB::table('ODS.TAB_ZONAEMP')
                ->where('idZonaEmp', $idZonaEmp)
                ->update(['idEstado' => 0]);

            // 3. Crear nuevo registro con el nuevo supervisor
            $nuevoIdZonaEmp = \DB::table('ODS.TAB_ZONAEMP')->insertGetId([
                'idZona' => $zonaEmpAntiguo->idZona,
                'idEmpleado' => $request->idEmpleadoNuevo,
                'idCiclo' => $request->idCiclo,
                'idEstado' => 1
            ]);

            // 4. Actualizar todos los registros de TAB_FUERZAVENTA que apuntaban al antiguo idZonaEmp
            $updated = \DB::table('ODS.TAB_FUERZAVENTA')
                ->where('idZonaEmp', $idZonaEmp)
                ->where('idCiclo', $request->idCiclo)
                ->update(['idZonaEmp' => $nuevoIdZonaEmp]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Supervisor cambiado exitosamente. Se actualizaron {$updated} registro(s) de fuerza de venta.",
                'nuevoIdZonaEmp' => $nuevoIdZonaEmp
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el supervisor: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cambia un representante médico.
     *
     * @param Request $request
     * @param int $idFuerza
     * @return JsonResponse
     */
    public function cambiarRepresentante(Request $request, int $idFuerza): JsonResponse
    {
        try {
            $request->validate([
                'idEmpleadoAntiguo' => 'required|integer',
                'idEmpleadoNuevo' => 'required|integer',
                'idCiclo' => 'required|integer'
            ]);

            \DB::beginTransaction();

            // ID del empleado VACANTE
            $idEmpleadoVacante = 338;

            // Obtener la zona donde se está haciendo el cambio (zona destino)
            $zonaDestino = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_ZONAEMP as ze', 'fv.idZonaEmp', '=', 'ze.idZonaEmp')
                ->where('fv.idFuerza', $idFuerza)
                ->select('ze.idZona')
                ->first();

            if (!$zonaDestino) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la zona destino.'
                ], 404);
            }

            // PASO 1: Cambiar los productos del empleado antiguo al nuevo en la zona destino
            $updated = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_ZONAEMP as ze', 'fv.idZonaEmp', '=', 'ze.idZonaEmp')
                ->where('fv.idEmpleado', $request->idEmpleadoAntiguo)
                ->where('ze.idZona', $zonaDestino->idZona)
                ->where('fv.idCiclo', $request->idCiclo)
                ->where('fv.idEstado', 1)
                ->update([
                    'idEmpleado' => $request->idEmpleadoNuevo
                ]);

            // PASO 2: Cambiar los productos del nuevo empleado en su zona anterior a VACANTE
            $zonasAnteriores = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_ZONAEMP as ze', 'fv.idZonaEmp', '=', 'ze.idZonaEmp')
                ->where('fv.idEmpleado', $request->idEmpleadoNuevo)
                ->where('ze.idZona', '!=', $zonaDestino->idZona)
                ->where('fv.idCiclo', $request->idCiclo)
                ->where('fv.idEstado', 1)
                ->update([
                    'idEmpleado' => $idEmpleadoVacante
                ]);

            \DB::commit();

            $mensaje = "Representante cambiado exitosamente. Se actualizaron {$updated} registro(s) en la zona destino.";
            if ($zonasAnteriores > 0) {
                $mensaje .= " {$zonasAnteriores} registro(s) en la zona anterior pasaron a VACANTE.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el representante: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtiene las líneas de representantes en una zona específica.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getLineasZona(int $id, Request $request): JsonResponse
    {
        try {
            $cicloId = $request->query('ciclo');
            
            if (!$cicloId) {
                // Obtener el ciclo actual
                $cicloActual = \DB::table('ODS.TAB_CICLO')
                    ->where('idEstado', 1)
                    ->orderBy('idCiclo', 'desc')
                    ->first();
                $cicloId = $cicloActual ? $cicloActual->idCiclo : null;
            }
            
            if (!$cicloId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un ciclo activo',
                    'data' => []
                ]);
            }
            
            // Obtener las líneas de la zona con sus empleados y productos
            $lineas = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_PRODUCTO as p', 'fv.idProducto', '=', 'p.idProducto')
                ->join('ODS.TAB_FRANQLINEA as fl', 'p.idFranqLinea', '=', 'fl.idFranqLinea')
                ->join('ODS.TAB_LINEA as l', 'fl.idLinea', '=', 'l.idLinea')
                ->join('ODS.TAB_EMPLEADO as e', 'fv.idEmpleado', '=', 'e.idEmpleado')
                ->join('ODS.TAB_ZONAEMP as ze', 'fv.idZonaEmp', '=', 'ze.idZonaEmp')
                ->where('ze.idZona', $id)
                ->where('fv.idCiclo', $cicloId)
                ->where('fv.idEstado', 1)
                ->select(
                    'fl.idFranqLinea',
                    'l.linea',
                    'fv.idEmpleado',
                    \DB::raw("CONCAT(e.nombre, ' ', ISNULL(e.apeNombre, '')) as empleado"),
                    \DB::raw("COUNT(DISTINCT p.idProducto) as productos")
                )
                ->groupBy('fl.idFranqLinea', 'l.linea', 'fv.idEmpleado', 'e.nombre', 'e.apeNombre')
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

    /**
     * Unifica dos líneas en una sola.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unificarLinea(Request $request): JsonResponse
    {
        try {
            \DB::beginTransaction();
            
            $idZona = $request->input('idZona');
            $idLinea = $request->input('idLinea');
            $idEmpleado = $request->input('idEmpleado');
            $lineasAUnificar = $request->input('lineasAUnificar', []);
            
            // Validaciones
            if (!$idZona || !$idLinea || !$idEmpleado || count($lineasAUnificar) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos incompletos para unificar líneas'
                ], 400);
            }
            
            // Obtener ciclo actual
            $cicloActual = \DB::table('ODS.TAB_CICLO')
                ->where('idEstado', 1)
                ->orderBy('idCiclo', 'desc')
                ->first();
            
            if (!$cicloActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un ciclo activo'
                ], 400);
            }
            
            // Obtener idZonaEmp
            $zonaEmp = \DB::table('ODS.TAB_ZONAEMP')
                ->where('idZona', $idZona)
                ->where('idCiclo', $cicloActual->idCiclo)
                ->first();
            
            if (!$zonaEmp) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la relación zona-empleado'
                ], 400);
            }
            
            // Buscar o crear FranqLinea con la nueva línea
            // Asumimos estructura y mixta por defecto (puedes ajustar según tu lógica)
            $franqLinea = \DB::table('ODS.TAB_FRANQLINEA')
                ->where('idLinea', $idLinea)
                ->where('idEstado', 1)
                ->first();
            
            if (!$franqLinea) {
                // Crear nueva FranqLinea si no existe
                $idFranqLinea = \DB::table('ODS.TAB_FRANQLINEA')->insertGetId([
                    'idLinea' => $idLinea,
                    'idMixta' => 1, // Ajustar según tu lógica
                    'idEstructura' => 1, // Ajustar según tu lógica
                    'idEstado' => 1
                ]);
            } else {
                $idFranqLinea = $franqLinea->idFranqLinea;
            }
            
            // Obtener todos los productos de las líneas a unificar con todos sus campos
            $productosOriginales = \DB::table('ODS.TAB_PRODUCTO')
                ->whereIn('idFranqLinea', $lineasAUnificar)
                ->where('idCiclo', $cicloActual->idCiclo)
                ->where('idEstado', 1)
                ->get();
            
            // Crear nuevos productos con el nuevo idFranqLinea
            $nuevosProductos = [];
            foreach ($productosOriginales as $productoOriginal) {
                // Verificar si ya existe el producto con la misma marca en la nueva línea
                $productoExistente = \DB::table('ODS.TAB_PRODUCTO')
                    ->where('idFranqLinea', $idFranqLinea)
                    ->where('idMarcaMkt', $productoOriginal->idMarcaMkt)
                    ->where('idCiclo', $cicloActual->idCiclo)
                    ->first();
                
                if (!$productoExistente) {
                    // Copiar todos los campos del producto original excepto idProducto
                    $datosProducto = [
                        'idCiclo' => $cicloActual->idCiclo,
                        'idFranqLinea' => $idFranqLinea,
                        'idMarcaMkt' => $productoOriginal->idMarcaMkt,
                        'idCore' => $productoOriginal->idCore,
                        'idCuota' => $productoOriginal->idCuota ?? null,
                        'idPromocion' => $productoOriginal->idPromocion ?? null,
                        'idAlcance' => $productoOriginal->idAlcance ?? null,
                        'idEstado' => 1,
                        'fechaModificacion' => now(),
                        'fechaCierre' => $productoOriginal->fechaCierre ?? null
                    ];
                    
                    $idProducto = \DB::table('ODS.TAB_PRODUCTO')->insertGetId($datosProducto);
                    $nuevosProductos[] = $idProducto;
                } else {
                    $nuevosProductos[] = $productoExistente->idProducto;
                }
            }
            
            // Desactivar registros antiguos en fuerzaventa
            \DB::table('ODS.TAB_FUERZAVENTA as fv')
                ->join('ODS.TAB_PRODUCTO as p', 'fv.idProducto', '=', 'p.idProducto')
                ->whereIn('p.idFranqLinea', $lineasAUnificar)
                ->where('fv.idZonaEmp', $zonaEmp->idZonaEmp)
                ->where('fv.idCiclo', $cicloActual->idCiclo)
                ->update(['fv.idEstado' => 0]);
            
            // Insertar nuevos registros en fuerzaventa
            foreach ($nuevosProductos as $idProducto) {
                \DB::table('ODS.TAB_FUERZAVENTA')->insert([
                    'idCiclo' => $cicloActual->idCiclo,
                    'idZonaEmp' => $zonaEmp->idZonaEmp,
                    'idProducto' => $idProducto,
                    'idEmpleado' => $idEmpleado,
                    'idEstado' => 1
                ]);
            }
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Líneas unificadas correctamente'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al unificar líneas: ' . $e->getMessage()
            ], 500);
        }
    }
}


