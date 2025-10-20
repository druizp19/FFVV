<?php

namespace App\Services;

use App\Repositories\CicloRepository;
use App\Models\Ciclo;
use App\Models\Producto;
use App\Models\ZonaEmp;
use App\Models\ZonaGeo;
use App\Models\FuerzaVenta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CicloService
{
    protected CicloRepository $cicloRepository;

    /**
     * Constructor del servicio.
     *
     * @param CicloRepository $cicloRepository
     */
    public function __construct(CicloRepository $cicloRepository)
    {
        $this->cicloRepository = $cicloRepository;
    }

    /**
     * Obtiene todos los ciclos.
     *
     * @return Collection
     */
    public function getAllCiclos(): Collection
    {
        return $this->cicloRepository->getAll();
    }

    /**
     * Obtiene un ciclo por su ID.
     *
     * @param int $id
     * @return Ciclo|null
     */
    public function getCicloById(int $id): ?Ciclo
    {
        return $this->cicloRepository->findById($id);
    }

    /**
     * Obtiene el último ciclo creado.
     *
     * @return Ciclo|null
     */
    public function getUltimoCiclo(): ?Ciclo
    {
        return $this->cicloRepository->getUltimoCiclo();
    }

    /**
     * Crea un nuevo ciclo.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function crearCiclo(array $data): array
    {
        // Validar que la fecha de fin sea mayor a la fecha de inicio
        $fechaInicio = Carbon::parse($data['fechaInicio']);
        $fechaFin = Carbon::parse($data['fechaFin']);

        if ($fechaFin->lessThanOrEqualTo($fechaInicio)) {
            return [
                'success' => false,
                'message' => 'La fecha de fin debe ser mayor a la fecha de inicio.'
            ];
        }

        // Verificar si existe solapamiento de fechas
        if ($this->cicloRepository->existeSolapamiento($data['fechaInicio'], $data['fechaFin'])) {
            return [
                'success' => false,
                'message' => 'Ya existe un ciclo con fechas solapadas.'
            ];
        }

        // Calcular días hábiles
        $diasHabiles = $fechaInicio->diffInDays($fechaFin);
        $data['diasHabiles'] = $diasHabiles;

        // Asignar estado por defecto (1 = Activo) si no viene en los datos
        if (!isset($data['idEstado'])) {
            $data['idEstado'] = 1;
        }

        try {
            $ciclo = $this->cicloRepository->create($data);

            return [
                'success' => true,
                'message' => 'Ciclo creado exitosamente.',
                'data' => $ciclo
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el ciclo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un ciclo existente.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function actualizarCiclo(int $id, array $data): array
    {
        // Validar que la fecha de fin sea mayor a la fecha de inicio
        $fechaInicio = Carbon::parse($data['fechaInicio']);
        $fechaFin = Carbon::parse($data['fechaFin']);

        if ($fechaFin->lessThanOrEqualTo($fechaInicio)) {
            return [
                'success' => false,
                'message' => 'La fecha de fin debe ser mayor a la fecha de inicio.'
            ];
        }

        // Verificar si existe solapamiento de fechas (excluyendo el ciclo actual)
        if ($this->cicloRepository->existeSolapamiento($data['fechaInicio'], $data['fechaFin'], $id)) {
            return [
                'success' => false,
                'message' => 'Ya existe un ciclo con fechas solapadas.'
            ];
        }

        // Calcular días hábiles
        $diasHabiles = $fechaInicio->diffInDays($fechaFin);
        $data['diasHabiles'] = $diasHabiles;

        try {
            $updated = $this->cicloRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Ciclo no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Ciclo actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el ciclo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un ciclo.
     *
     * @param int $id
     * @return array
     */
    public function eliminarCiclo(int $id): array
    {
        try {
            $deleted = $this->cicloRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Ciclo no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Ciclo eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el ciclo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Copia un ciclo completo con todas sus relaciones.
     * Clona: Productos, ZonaEmp, ZonaGeo y FuerzaVenta con nuevos IDs.
     *
     * @param int $idCicloOriginal
     * @param array $datosCicloNuevo
     * @return array
     */
    public function copiarCicloCompleto(int $idCicloOriginal, array $datosCicloNuevo): array
    {
        // Validar que el ciclo original existe
        $cicloOriginal = $this->cicloRepository->findById($idCicloOriginal);

        if (!$cicloOriginal) {
            return [
                'success' => false,
                'message' => 'Ciclo original no encontrado.'
            ];
        }

        // Validar fechas del nuevo ciclo
        $fechaInicio = Carbon::parse($datosCicloNuevo['fechaInicio']);
        $fechaFin = Carbon::parse($datosCicloNuevo['fechaFin']);

        if ($fechaFin->lessThanOrEqualTo($fechaInicio)) {
            return [
                'success' => false,
                'message' => 'La fecha de fin debe ser mayor a la fecha de inicio.'
            ];
        }

        // Verificar solapamiento de fechas
        if ($this->cicloRepository->existeSolapamiento($datosCicloNuevo['fechaInicio'], $datosCicloNuevo['fechaFin'])) {
            return [
                'success' => false,
                'message' => 'Ya existe un ciclo con fechas solapadas.'
            ];
        }

        // Aumentar el límite de tiempo de ejecución para operaciones grandes
        set_time_limit(300); // 5 minutos
        ini_set('memory_limit', '512M'); // Aumentar memoria disponible

        DB::beginTransaction();

        try {
            // 1. CREAR EL NUEVO CICLO
            $diasHabiles = $fechaInicio->diffInDays($fechaFin);
            $datosCicloNuevo['diasHabiles'] = $diasHabiles;
            
            // Asignar estado por defecto (1 = Activo) si no viene en los datos
            if (!isset($datosCicloNuevo['idEstado'])) {
                $datosCicloNuevo['idEstado'] = 1;
            }
            
            $cicloNuevo = $this->cicloRepository->create($datosCicloNuevo);

            Log::info("Ciclo {$cicloNuevo->idCiclo} creado. Iniciando clonación desde ciclo {$idCicloOriginal}");

            // 2. CLONAR PRODUCTOS
            $mapeoProductos = $this->clonarProductos($cicloOriginal, $cicloNuevo);
            
            // 3. CLONAR ZONA-EMPLEADOS
            $this->clonarZonasEmpleados($cicloOriginal, $cicloNuevo);
            
            // 4. CLONAR ZONA-GEOSEGMENTOS
            $this->clonarZonasGeosegmentos($cicloOriginal, $cicloNuevo);
            
            // 5. CLONAR FUERZA DE VENTA (con los nuevos IDs de productos)
            $this->clonarFuerzaVenta($cicloOriginal, $cicloNuevo, $mapeoProductos);

            DB::commit();

            Log::info("Clonación completada exitosamente. Ciclo {$cicloNuevo->idCiclo} creado con todas sus relaciones.");

            return [
                'success' => true,
                'message' => "Ciclo clonado exitosamente. Se creó el ciclo {$cicloNuevo->idCiclo} con todas sus relaciones.",
                'data' => [
                    'ciclo' => $cicloNuevo,
                    'productos_clonados' => count($mapeoProductos),
                    'estadisticas' => [
                        'productos' => count($mapeoProductos),
                        'zonas_empleados' => ZonaEmp::where('idCiclo', $cicloNuevo->idCiclo)->count(),
                        'zonas_geosegmentos' => ZonaGeo::where('idCiclo', $cicloNuevo->idCiclo)->count(),
                        'fuerzas_venta' => FuerzaVenta::where('idCiclo', $cicloNuevo->idCiclo)->count(),
                    ]
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Error al clonar ciclo: " . $e->getMessage(), [
                'ciclo_original' => $idCicloOriginal,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al clonar el ciclo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clona todos los productos de un ciclo a otro (OPTIMIZADO con bulk insert y chunks).
     *
     * @param Ciclo $cicloOriginal
     * @param Ciclo $cicloNuevo
     * @return array Mapeo de IDs antiguos a nuevos [idProductoViejo => idProductoNuevo]
     */
    protected function clonarProductos(Ciclo $cicloOriginal, Ciclo $cicloNuevo): array
    {
        $now = Carbon::now();
        $mapeoProductos = [];
        $totalClonados = 0;
        
        // SQL Server limita a 2100 parámetros por query
        // Con 10 columnas por producto: 2100/10 = 210 registros máximo
        // Usamos 200 para estar seguros
        DB::table('ODS.TAB_PRODUCTO')
            ->where('idCiclo', $cicloOriginal->idCiclo)
            ->orderBy('idProducto')
            ->chunk(200, function ($productosOriginales) use ($cicloNuevo, $now, &$mapeoProductos, &$totalClonados) {
                $productosParaInsertar = [];
                $idsOriginales = [];
                
                foreach ($productosOriginales as $producto) {
                    $idsOriginales[] = $producto->idProducto;
                    $productosParaInsertar[] = [
                        'idCiclo' => $cicloNuevo->idCiclo,
                        'idFranqLinea' => $producto->idFranqLinea,
                        'idMarcaMkt' => $producto->idMarcaMkt,
                        'idCore' => $producto->idCore,
                        'idCuota' => $producto->idCuota,
                        'idPromocion' => $producto->idPromocion,
                        'idAlcance' => $producto->idAlcance,
                        'idEstado' => $producto->idEstado,
                        'fechaModificacion' => $now,
                        'fechaCierre' => null,
                    ];
                }
                
                // Inserción masiva del lote
                if (!empty($productosParaInsertar)) {
                    DB::table('ODS.TAB_PRODUCTO')->insert($productosParaInsertar);
                    
                    // Obtener los IDs de los productos recién insertados en este lote
                    $productosNuevos = DB::table('ODS.TAB_PRODUCTO')
                        ->where('idCiclo', $cicloNuevo->idCiclo)
                        ->orderBy('idProducto', 'desc')
                        ->limit(count($productosParaInsertar))
                        ->get(['idProducto'])
                        ->reverse()
                        ->values();
                    
                    // Crear mapeo para este lote
                    for ($i = 0; $i < count($idsOriginales); $i++) {
                        if (isset($productosNuevos[$i])) {
                            $mapeoProductos[$idsOriginales[$i]] = $productosNuevos[$i]->idProducto;
                        }
                    }
                    
                    $totalClonados += count($productosParaInsertar);
                }
            });

        Log::info("Productos clonados (bulk): {$totalClonados}");

        return $mapeoProductos;
    }

    /**
     * Clona todas las asignaciones zona-empleado de un ciclo a otro (OPTIMIZADO con bulk insert y chunks).
     *
     * @param Ciclo $cicloOriginal
     * @param Ciclo $cicloNuevo
     * @return void
     */
    protected function clonarZonasEmpleados(Ciclo $cicloOriginal, Ciclo $cicloNuevo): void
    {
        $totalClonados = 0;
        
        // SQL Server limita a 2100 parámetros por query
        // Con 4 columnas por zona: 2100/4 = 525 registros máximo
        // Usamos 500 para estar seguros
        DB::table('ODS.TAB_ZONAEMP')
            ->where('idCiclo', $cicloOriginal->idCiclo)
            ->orderBy('idZonaEmp')
            ->chunk(500, function ($zonasEmpOriginales) use ($cicloNuevo, &$totalClonados) {
                $zonasParaInsertar = [];
                
                foreach ($zonasEmpOriginales as $zona) {
                    $zonasParaInsertar[] = [
                        'idCiclo' => $cicloNuevo->idCiclo,
                        'idZona' => $zona->idZona,
                        'idEmpleado' => $zona->idEmpleado,
                        'idEstado' => $zona->idEstado,
                    ];
                }
                
                if (!empty($zonasParaInsertar)) {
                    DB::table('ODS.TAB_ZONAEMP')->insert($zonasParaInsertar);
                    $totalClonados += count($zonasParaInsertar);
                }
            });

        Log::info("ZonasEmpleados clonadas (bulk): {$totalClonados}");
    }

    /**
     * Clona todas las asignaciones zona-geosegmento de un ciclo a otro (OPTIMIZADO con bulk insert y chunks).
     *
     * @param Ciclo $cicloOriginal
     * @param Ciclo $cicloNuevo
     * @return void
     */
    protected function clonarZonasGeosegmentos(Ciclo $cicloOriginal, Ciclo $cicloNuevo): void
    {
        $totalClonados = 0;
        
        // SQL Server limita a 2100 parámetros por query
        // Con 4 columnas por zona: 2100/4 = 525 registros máximo
        // Usamos 500 para estar seguros
        DB::table('ODS.TAB_ZONAGEO')
            ->where('idCiclo', $cicloOriginal->idCiclo)
            ->orderBy('idZonaGeo')
            ->chunk(500, function ($zonasGeoOriginales) use ($cicloNuevo, &$totalClonados) {
                $zonasParaInsertar = [];
                
                foreach ($zonasGeoOriginales as $zona) {
                    $zonasParaInsertar[] = [
                        'idZona' => $zona->idZona,
                        'idGeosegmento' => $zona->idGeosegmento,
                        'idEstado' => $zona->idEstado,
                        'idCiclo' => $cicloNuevo->idCiclo,
                    ];
                }
                
                if (!empty($zonasParaInsertar)) {
                    DB::table('ODS.TAB_ZONAGEO')->insert($zonasParaInsertar);
                    $totalClonados += count($zonasParaInsertar);
                }
            });

        Log::info("ZonasGeosegmentos clonadas (bulk): {$totalClonados}");
    }

    /**
     * Clona todas las fuerzas de venta de un ciclo a otro (OPTIMIZADO con bulk insert y chunks).
     * Usa el mapeo de productos para actualizar las referencias.
     *
     * @param Ciclo $cicloOriginal
     * @param Ciclo $cicloNuevo
     * @param array $mapeoProductos
     * @return void
     */
    protected function clonarFuerzaVenta(Ciclo $cicloOriginal, Ciclo $cicloNuevo, array $mapeoProductos): void
    {
        $now = Carbon::now();
        $totalClonados = 0;
        
        // Calcular el nuevo periodo de comisión basado en la fecha de inicio del nuevo ciclo
        $fechaInicioCicloNuevo = Carbon::parse($cicloNuevo->fechaInicio);
        $nuevoPeriodoComision = $fechaInicioCicloNuevo->format('Ym'); // Formato YYYYMM (202510)
        
        // SQL Server limita a 2100 parámetros por query
        // Con 8 columnas por fuerza de venta: 2100/8 = 262 registros máximo
        // Usamos 250 para estar seguros
        DB::table('ODS.TAB_FUERZAVENTA')
            ->where('idCiclo', $cicloOriginal->idCiclo)
            ->orderBy('idFuerza')
            ->chunk(250, function ($fuerzasVentaOriginales) use ($cicloNuevo, $mapeoProductos, $now, $nuevoPeriodoComision, &$totalClonados) {
                $fuerzasParaInsertar = [];
                
                foreach ($fuerzasVentaOriginales as $fuerza) {
                    // Verificar si el producto original tiene mapeo
                    if (isset($mapeoProductos[$fuerza->idProducto])) {
                        $fuerzasParaInsertar[] = [
                            'idCiclo' => $cicloNuevo->idCiclo,
                            'idZonaEmp' => $fuerza->idZonaEmp,
                            'idProducto' => $mapeoProductos[$fuerza->idProducto],
                            'idEmpleado' => $fuerza->idEmpleado,
                            'fechaModificacion' => $now,
                            'fechaCierre' => null,
                            'idEstado' => $fuerza->idEstado,
                            'periodoComision' => $nuevoPeriodoComision, // Calculado automáticamente según fecha del ciclo
                        ];
                    }
                }
                
                // Inserción masiva del lote
                if (!empty($fuerzasParaInsertar)) {
                    DB::table('ODS.TAB_FUERZAVENTA')->insert($fuerzasParaInsertar);
                    $totalClonados += count($fuerzasParaInsertar);
                }
            });

        Log::info("FuerzasVenta clonadas (bulk): {$totalClonados} - Periodo: {$nuevoPeriodoComision}");
    }

    /**
     * Copia la configuración de un ciclo a uno nuevo (método simple).
     * NOTA: Use copiarCicloCompleto() para clonar con todas las relaciones.
     *
     * @param int $idCicloOriginal
     * @param array $nuevasFechas
     * @return array
     */
    public function copiarCiclo(int $idCicloOriginal, array $nuevasFechas): array
    {
        $cicloOriginal = $this->cicloRepository->findById($idCicloOriginal);

        if (!$cicloOriginal) {
            return [
                'success' => false,
                'message' => 'Ciclo original no encontrado.'
            ];
        }

        // Crear nuevo ciclo con las nuevas fechas (sin clonar relaciones)
        return $this->crearCiclo($nuevasFechas);
    }
}



