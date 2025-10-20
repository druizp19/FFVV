<?php

namespace App\Services;

use App\Repositories\MixtaRepository;
use App\Models\Mixta;
use Illuminate\Database\Eloquent\Collection;

class MixtaService
{
    protected MixtaRepository $mixtaRepository;

    public function __construct(MixtaRepository $mixtaRepository)
    {
        $this->mixtaRepository = $mixtaRepository;
    }

    public function getAllMixtas(): Collection
    {
        return $this->mixtaRepository->getAll();
    }

    public function getMixtaById(int $id): ?Mixta
    {
        return $this->mixtaRepository->findById($id);
    }

    public function crearMixta(array $data): array
    {
        try {
            $mixta = $this->mixtaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Mixta creada exitosamente.',
                'data' => $mixta
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la mixta: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarMixta(int $id, array $data): array
    {
        try {
            $updated = $this->mixtaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Mixta no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Mixta actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la mixta: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarMixta(int $id): array
    {
        try {
            $deleted = $this->mixtaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Mixta no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Mixta eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la mixta: ' . $e->getMessage()
            ];
        }
    }
}

