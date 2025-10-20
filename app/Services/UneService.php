<?php

namespace App\Services;

use App\Repositories\UneRepository;
use App\Models\Une;
use Illuminate\Database\Eloquent\Collection;

class UneService
{
    protected UneRepository $uneRepository;

    public function __construct(UneRepository $uneRepository)
    {
        $this->uneRepository = $uneRepository;
    }

    public function getAllUnes(): Collection
    {
        return $this->uneRepository->getAll();
    }

    public function getUneById(int $id): ?Une
    {
        return $this->uneRepository->findById($id);
    }

    public function crearUne(array $data): array
    {
        try {
            $une = $this->uneRepository->create($data);

            return [
                'success' => true,
                'message' => 'Unidad de negocio creada exitosamente.',
                'data' => $une
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la unidad de negocio: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarUne(int $id, array $data): array
    {
        try {
            $updated = $this->uneRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Unidad de negocio no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Unidad de negocio actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la unidad de negocio: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarUne(int $id): array
    {
        try {
            $deleted = $this->uneRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Unidad de negocio no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Unidad de negocio eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la unidad de negocio: ' . $e->getMessage()
            ];
        }
    }
}

