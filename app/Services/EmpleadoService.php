<?php

namespace App\Services;

use App\Repositories\EmpleadoRepository;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Collection;

class EmpleadoService
{
    protected EmpleadoRepository $empleadoRepository;

    public function __construct(EmpleadoRepository $empleadoRepository)
    {
        $this->empleadoRepository = $empleadoRepository;
    }

    public function getAllEmpleados(): Collection
    {
        return $this->empleadoRepository->getAll();
    }

    public function getEmpleadoById(int $id): ?Empleado
    {
        return $this->empleadoRepository->findById($id);
    }

    public function crearEmpleado(array $data): array
    {
        // Validar que el DNI no exista
        $empleadoExistente = $this->empleadoRepository->findByDni($data['dni']);
        
        if ($empleadoExistente) {
            return [
                'success' => false,
                'message' => 'Ya existe un empleado con ese DNI.'
            ];
        }

        try {
            $empleado = $this->empleadoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Empleado creado exitosamente.',
                'data' => $empleado
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el empleado: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarEmpleado(int $id, array $data): array
    {
        // Validar que el DNI no exista en otro empleado
        if (isset($data['dni'])) {
            $empleadoExistente = $this->empleadoRepository->findByDni($data['dni']);
            
            if ($empleadoExistente && $empleadoExistente->idEmpleado !== $id) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un empleado con ese DNI.'
                ];
            }
        }

        try {
            $updated = $this->empleadoRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Empleado no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Empleado actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el empleado: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarEmpleado(int $id): array
    {
        try {
            $deleted = $this->empleadoRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Empleado no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Empleado eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el empleado: ' . $e->getMessage()
            ];
        }
    }

    public function buscarEmpleado(string $termino): Collection
    {
        return $this->empleadoRepository->buscarPorNombre($termino);
    }
}

