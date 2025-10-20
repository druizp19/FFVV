<?php

namespace App\Services;

use App\Repositories\MarcaRepository;
use App\Models\Marca;
use Illuminate\Database\Eloquent\Collection;

class MarcaService
{
    protected MarcaRepository $marcaRepository;

    public function __construct(MarcaRepository $marcaRepository)
    {
        $this->marcaRepository = $marcaRepository;
    }

    public function getAllMarcas(): Collection
    {
        return $this->marcaRepository->getAll();
    }

    public function getMarcaById(int $id): ?Marca
    {
        return $this->marcaRepository->findById($id);
    }

    public function crearMarca(array $data): array
    {
        try {
            $marca = $this->marcaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Marca creada exitosamente.',
                'data' => $marca
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la marca: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarMarca(int $id, array $data): array
    {
        try {
            $updated = $this->marcaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Marca no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Marca actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la marca: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarMarca(int $id): array
    {
        try {
            $deleted = $this->marcaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Marca no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Marca eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la marca: ' . $e->getMessage()
            ];
        }
    }
}

