<?php

namespace App\Services;

use App\Repositories\ZonaEmpRepository;
use App\Models\ZonaEmp;
use Illuminate\Database\Eloquent\Collection;

class ZonaEmpService
{
    protected ZonaEmpRepository $zonaEmpRepository;

    public function __construct(ZonaEmpRepository $zonaEmpRepository)
    {
        $this->zonaEmpRepository = $zonaEmpRepository;
    }

    public function getAllZonasEmpleados(): Collection
    {
        return $this->zonaEmpRepository->getAll();
    }

    public function getZonaEmpById(int $id): ?ZonaEmp
    {
        return $this->zonaEmpRepository->findById($id);
    }

    public function crearZonaEmp(array $data): array
    {
        try {
            $zonaEmp = $this->zonaEmpRepository->create($data);

            return [
                'success' => true,
                'message' => 'Asignación Zona-Empleado creada exitosamente.',
                'data' => $zonaEmp
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarZonaEmp(int $id, array $data): array
    {
        try {
            $updated = $this->zonaEmpRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Asignación Zona-Empleado no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Zona-Empleado actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarZonaEmp(int $id): array
    {
        try {
            $deleted = $this->zonaEmpRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Asignación Zona-Empleado no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Zona-Empleado eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ];
        }
    }
}

