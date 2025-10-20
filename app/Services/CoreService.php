<?php

namespace App\Services;

use App\Repositories\CoreRepository;
use App\Models\Core;
use Illuminate\Database\Eloquent\Collection;

class CoreService
{
    protected CoreRepository $coreRepository;

    public function __construct(CoreRepository $coreRepository)
    {
        $this->coreRepository = $coreRepository;
    }

    public function getAllCores(): Collection
    {
        return $this->coreRepository->getAll();
    }

    public function getCoreById(int $id): ?Core
    {
        return $this->coreRepository->findById($id);
    }

    public function crearCore(array $data): array
    {
        try {
            $core = $this->coreRepository->create($data);

            return [
                'success' => true,
                'message' => 'Core creado exitosamente.',
                'data' => $core
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el core: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarCore(int $id, array $data): array
    {
        try {
            $updated = $this->coreRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Core no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Core actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el core: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarCore(int $id): array
    {
        try {
            $deleted = $this->coreRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Core no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Core eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el core: ' . $e->getMessage()
            ];
        }
    }
}

