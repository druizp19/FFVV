<?php

namespace App\Services;

use App\Repositories\ZonaRepository;
use App\Models\Zona;
use App\Models\ZonaGeo;
use App\Models\Geosegmento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ZonaService
{
    protected ZonaRepository $zonaRepository;

    public function __construct(ZonaRepository $zonaRepository)
    {
        $this->zonaRepository = $zonaRepository;
    }

    public function getAllZonas(): Collection
    {
        return $this->zonaRepository->getAll();
    }

    public function getZonaById(int $id): ?Zona
    {
        return $this->zonaRepository->findById($id);
    }

    public function crearZona(array $data): array
    {
        try {
            DB::beginTransaction();
            
            // Crear la zona
            $zona = $this->zonaRepository->create([
                'zona' => $data['zona'],
                'idEstado' => $data['idEstado'] ?? 1 // Activo por defecto
            ]);

            // Asignar geosegmentos si existen
            if (isset($data['geosegmentos']) && is_array($data['geosegmentos'])) {
                foreach ($data['geosegmentos'] as $geoData) {
                    $idGeosegmento = $geoData['idGeosegmento'];
                    
                    // Si es un nuevo geosegmento, crearlo primero
                    if (isset($geoData['nuevoGeosegmento']) && $geoData['nuevoGeosegmento']) {
                        $nuevoGeo = Geosegmento::create([
                            'geosegmento' => $geoData['nuevoGeosegmento'],
                            'lugar' => $geoData['nuevoLugar'],
                            'idEstado' => 1 // Activo por defecto
                        ]);
                        $idGeosegmento = $nuevoGeo->idGeosegmento;
                    }
                    
                    // Crear la relación zona-geosegmento
                    ZonaGeo::create([
                        'idZona' => $zona->idZona,
                        'idGeosegmento' => $idGeosegmento,
                        'idCiclo' => $geoData['idCiclo'],
                        'idEstado' => 1 // Activo por defecto
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Zona creada exitosamente.',
                'data' => $zona
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear la zona: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarZona(int $id, array $data): array
    {
        try {
            $updated = $this->zonaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Zona no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Zona actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la zona: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarZona(int $id): array
    {
        try {
            $deleted = $this->zonaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Zona no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Zona eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la zona: ' . $e->getMessage()
            ];
        }
    }
}

