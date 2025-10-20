<?php

namespace App\Services;

use App\Repositories\AreaRepository;
use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;

class AreaService
{
    protected AreaRepository $areaRepository;

    public function __construct(AreaRepository $areaRepository)
    {
        $this->areaRepository = $areaRepository;
    }

    public function getAllAreas(): Collection
    {
        return $this->areaRepository->getAll();
    }

    public function getAreaById(int $id): ?Area
    {
        return $this->areaRepository->findById($id);
    }

    public function crearArea(array $data): array
    {
        try {
            $area = $this->areaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Área creada exitosamente.',
                'data' => $area
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el área: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarArea(int $id, array $data): array
    {
        try {
            $updated = $this->areaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Área no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Área actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el área: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarArea(int $id): array
    {
        try {
            $deleted = $this->areaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Área no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Área eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el área: ' . $e->getMessage()
            ];
        }
    }
}

