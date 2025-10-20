<?php

namespace App\Services;

use App\Repositories\PromocionRepository;
use App\Models\Promocion;
use Illuminate\Database\Eloquent\Collection;

class PromocionService
{
    protected PromocionRepository $promocionRepository;

    public function __construct(PromocionRepository $promocionRepository)
    {
        $this->promocionRepository = $promocionRepository;
    }

    public function getAllPromociones(): Collection
    {
        return $this->promocionRepository->getAll();
    }

    public function getPromocionById(int $id): ?Promocion
    {
        return $this->promocionRepository->findById($id);
    }

    public function crearPromocion(array $data): array
    {
        try {
            $promocion = $this->promocionRepository->create($data);

            return [
                'success' => true,
                'message' => 'Promoción creada exitosamente.',
                'data' => $promocion
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la promoción: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarPromocion(int $id, array $data): array
    {
        try {
            $updated = $this->promocionRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Promoción no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Promoción actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la promoción: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarPromocion(int $id): array
    {
        try {
            $deleted = $this->promocionRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Promoción no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Promoción eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la promoción: ' . $e->getMessage()
            ];
        }
    }
}

