<?php

namespace App\Services;

use App\Repositories\CargoRepository;
use App\Models\Cargo;
use Illuminate\Database\Eloquent\Collection;

class CargoService
{
    protected CargoRepository $cargoRepository;

    public function __construct(CargoRepository $cargoRepository)
    {
        $this->cargoRepository = $cargoRepository;
    }

    public function getAllCargos(): Collection
    {
        return $this->cargoRepository->getAll();
    }

    public function getCargoById(int $id): ?Cargo
    {
        return $this->cargoRepository->findById($id);
    }

    public function crearCargo(array $data): array
    {
        try {
            $cargo = $this->cargoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Cargo creado exitosamente.',
                'data' => $cargo
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el cargo: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarCargo(int $id, array $data): array
    {
        try {
            $updated = $this->cargoRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Cargo no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Cargo actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el cargo: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarCargo(int $id): array
    {
        try {
            $deleted = $this->cargoRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Cargo no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Cargo eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el cargo: ' . $e->getMessage()
            ];
        }
    }
}

