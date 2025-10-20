<?php

namespace App\Services;

use App\Repositories\ZonaRepository;
use App\Models\Zona;
use Illuminate\Database\Eloquent\Collection;

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
            $zona = $this->zonaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Zona creada exitosamente.',
                'data' => $zona
            ];
        } catch (\Exception $e) {
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

