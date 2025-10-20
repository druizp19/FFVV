<?php

namespace App\Services;

use App\Repositories\EstadoRepository;
use App\Models\Estado;
use Illuminate\Database\Eloquent\Collection;

class EstadoService
{
    protected EstadoRepository $estadoRepository;

    public function __construct(EstadoRepository $estadoRepository)
    {
        $this->estadoRepository = $estadoRepository;
    }

    public function getAllEstados(): Collection
    {
        return $this->estadoRepository->getAll();
    }

    public function getEstadoById(int $id): ?Estado
    {
        return $this->estadoRepository->findById($id);
    }

    public function crearEstado(array $data): array
    {
        try {
            $estado = $this->estadoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Estado creado exitosamente.',
                'data' => $estado
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el estado: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarEstado(int $id, array $data): array
    {
        try {
            $updated = $this->estadoRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Estado no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Estado actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarEstado(int $id): array
    {
        try {
            $deleted = $this->estadoRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Estado no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Estado eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el estado: ' . $e->getMessage()
            ];
        }
    }
}

