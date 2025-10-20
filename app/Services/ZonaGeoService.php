<?php

namespace App\Services;

use App\Repositories\ZonaGeoRepository;
use App\Models\ZonaGeo;
use Illuminate\Database\Eloquent\Collection;

class ZonaGeoService
{
    protected ZonaGeoRepository $zonaGeoRepository;

    public function __construct(ZonaGeoRepository $zonaGeoRepository)
    {
        $this->zonaGeoRepository = $zonaGeoRepository;
    }

    public function getAllZonasGeosegmentos(): Collection
    {
        return $this->zonaGeoRepository->getAll();
    }

    public function getZonaGeoById(int $id): ?ZonaGeo
    {
        return $this->zonaGeoRepository->findById($id);
    }

    public function crearZonaGeo(array $data): array
    {
        try {
            $zonaGeo = $this->zonaGeoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Asignación Zona-Geosegmento creada exitosamente.',
                'data' => $zonaGeo
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarZonaGeo(int $id, array $data): array
    {
        try {
            $updated = $this->zonaGeoRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Asignación Zona-Geosegmento no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Zona-Geosegmento actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarZonaGeo(int $id): array
    {
        try {
            $deleted = $this->zonaGeoRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Asignación Zona-Geosegmento no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Zona-Geosegmento eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ];
        }
    }
}

