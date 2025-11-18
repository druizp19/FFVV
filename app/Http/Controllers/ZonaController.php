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
        
        // Obtener zonas con paginación y mantener parámetros
        $zonas = $query->orderBy('idZona', 'desc')
            ->paginate(10)
            ->appends($request->all());
        
        // Agregar el conteo de ubigeos manualmente para cada zona
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
                'fv.idZonaEmp'
            )
            ->groupBy('fv.idEmpleado', 'e.nombre', 'e.apeNombre', 'e.correo', 'c.cargo', 'fv.idZonaEmp') // Agrupar por empleado
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
                    'supervisor' => $zonaEmp->empleado->nombre ?? 'N/A'
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
                'idProducto' => 'nullable|integer'
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

            // Crear nueva relación en TAB_FUERZAVENTA
            $idFuerza = \DB::table('ODS.TAB_FUERZAVENTA')->insertGetId([
                'idCiclo' => $request->idCiclo,
                'idZonaEmp' => $request->idZonaEmp,
                'idProducto' => $request->idProducto,
                'idEmpleado' => $request->idEmpleado,
                'fechaModificacion' => now(),
                'fechaCierre' => null,
                'idEstado' => 1,
                'periodoComision' => $periodoComision
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Representante médico agregado exitosamente.',
                'idFuerza' => $idFuerza
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
            
            foreach ($zona->zonasEmpleados as $zonaEmp) {
                \Log::error('DEBUG: Buscando representantes para supervisor', [
                    'idZonaEmp' => $zonaEmp->idZonaEmp,
                    'supervisor' => $zonaEmp->empleado->nombre ?? 'N/A'
                ]);
                
                $repsQuery = \DB::table('ODS.TAB_FUERZAVENTA as fv')
                    ->join('ODS.TAB_EMPLEADO as e', 'fv.idEmpleado', '=', 'e.idEmpleado')
                    ->leftJoin('ODS.TAB_CARGO as c', 'e.idCargo', '=', 'c.idCargo')
                    ->where('fv.idZonaEmp', $zonaEmp->idZonaEmp)
                    ->where('fv.idEstado', 1);
                
                if ($cicloId) {
                    $repsQuery->where('fv.idCiclo', $cicloId);
                }
                
                // Log de la query SQL
                \Log::error('DEBUG: Query SQL', [
                    'sql' => $repsQuery->toSql(),
                    'bindings' => [
                        'idZonaEmp' => $zonaEmp->idZonaEmp,
                        'idEstado' => 1,
                        'idCiclo' => $cicloId
                    ]
                ]);
                
                $reps = $repsQuery->select(
                    \DB::raw('MIN(fv.idFuerza) as idFuerza'),
                    'fv.idEmpleado',
                    'e.nombre',
                    'e.apeNombre',
                    'c.cargo'
                )
                ->groupBy('fv.idEmpleado', 'e.nombre', 'e.apeNombre', 'c.cargo') // Agrupar por empleado
                ->get();
                
                \Log::error('DEBUG: Representantes encontrados', [
                    'idZonaEmp' => $zonaEmp->idZonaEmp,
                    'count' => $reps->count(),
                    'representantes' => $reps->toArray()
                ]);
                
                foreach ($reps as $rep) {
                    $representantes->push([
                        'id' => $rep->idFuerza,
                        'nombre' => trim(($rep->nombre ?? '') . ' ' . ($rep->apeNombre ?? '')),
                        'tipo' => 'representante',
                        'cargo' => $rep->cargo ?? 'Representante Médico',
                        'supervisor' => $zonaEmp->empleado->nombre ?? 'N/A'
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
}


