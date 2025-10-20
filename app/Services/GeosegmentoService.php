<?php

namespace App\Services;

use App\Repositories\GeosegmentoRepository;
use App\Models\Geosegmento;
use Illuminate\Database\Eloquent\Collection;

class GeosegmentoService
{
    protected GeosegmentoRepository $geosegmentoRepository;

    public function __construct(GeosegmentoRepository $geosegmentoRepository)
    {
        $this->geosegmentoRepository = $geosegmentoRepository;
    }

    public function getAllGeosegmentos(): Collection
    {
        return $this->geosegmentoRepository->getAll();
    }

    public function getGeosegmentoById(int $id): ?Geosegmento
    {
        return $this->geosegmentoRepository->findById($id);
    }

    public function crearGeosegmento(array $data): array
    {
        try {
            $geosegmento = $this->geosegmentoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Geosegmento creado exitosamente.',
                'data' => $geosegmento
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el geosegmento: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarGeosegmento(int $id, array $data): array
    {
        try {
            $updated = $this->geosegmentoRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Geosegmento no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Geosegmento actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el geosegmento: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarGeosegmento(int $id): array
    {
        try {
            $deleted = $this->geosegmentoRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Geosegmento no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Geosegmento eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el geosegmento: ' . $e->getMessage()
            ];
        }
    }
}

