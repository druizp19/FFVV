<?php

namespace App\Services;

use App\Repositories\AlcanceRepository;
use App\Models\Alcance;
use Illuminate\Database\Eloquent\Collection;

class AlcanceService
{
    protected AlcanceRepository $alcanceRepository;

    public function __construct(AlcanceRepository $alcanceRepository)
    {
        $this->alcanceRepository = $alcanceRepository;
    }

    public function getAllAlcances(): Collection
    {
        return $this->alcanceRepository->getAll();
    }

    public function getAlcanceById(int $id): ?Alcance
    {
        return $this->alcanceRepository->findById($id);
    }

    public function crearAlcance(array $data): array
    {
        try {
            $alcance = $this->alcanceRepository->create($data);

            return [
                'success' => true,
                'message' => 'Alcance creado exitosamente.',
                'data' => $alcance
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el alcance: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarAlcance(int $id, array $data): array
    {
        try {
            $updated = $this->alcanceRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Alcance no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Alcance actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el alcance: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarAlcance(int $id): array
    {
        try {
            $deleted = $this->alcanceRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Alcance no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Alcance eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el alcance: ' . $e->getMessage()
            ];
        }
    }
}

