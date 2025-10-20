<?php

namespace App\Services;

use App\Repositories\MercadoRepository;
use App\Models\Mercado;
use Illuminate\Database\Eloquent\Collection;

class MercadoService
{
    protected MercadoRepository $mercadoRepository;

    public function __construct(MercadoRepository $mercadoRepository)
    {
        $this->mercadoRepository = $mercadoRepository;
    }

    public function getAllMercados(): Collection
    {
        return $this->mercadoRepository->getAll();
    }

    public function getMercadoById(int $id): ?Mercado
    {
        return $this->mercadoRepository->findById($id);
    }

    public function crearMercado(array $data): array
    {
        try {
            $mercado = $this->mercadoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Mercado creado exitosamente.',
                'data' => $mercado
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el mercado: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarMercado(int $id, array $data): array
    {
        try {
            $updated = $this->mercadoRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Mercado no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Mercado actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el mercado: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarMercado(int $id): array
    {
        try {
            $deleted = $this->mercadoRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Mercado no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Mercado eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el mercado: ' . $e->getMessage()
            ];
        }
    }
}

