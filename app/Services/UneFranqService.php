<?php

namespace App\Services;

use App\Repositories\UneFranqRepository;
use App\Models\UneFranq;
use Illuminate\Database\Eloquent\Collection;

class UneFranqService
{
    protected UneFranqRepository $uneFranqRepository;

    public function __construct(UneFranqRepository $uneFranqRepository)
    {
        $this->uneFranqRepository = $uneFranqRepository;
    }

    public function getAllUnesFranquicias(): Collection
    {
        return $this->uneFranqRepository->getAll();
    }

    public function getUneFranqById(int $id): ?UneFranq
    {
        return $this->uneFranqRepository->findById($id);
    }

    public function crearUneFranq(array $data): array
    {
        try {
            $uneFranq = $this->uneFranqRepository->create($data);

            return [
                'success' => true,
                'message' => 'Asignación UNE-Franquicia creada exitosamente.',
                'data' => $uneFranq
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarUneFranq(int $id, array $data): array
    {
        try {
            $updated = $this->uneFranqRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Asignación UNE-Franquicia no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación UNE-Franquicia actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarUneFranq(int $id): array
    {
        try {
            $deleted = $this->uneFranqRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Asignación UNE-Franquicia no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación UNE-Franquicia eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ];
        }
    }
}

