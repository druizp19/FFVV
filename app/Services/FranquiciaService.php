<?php

namespace App\Services;

use App\Repositories\FranquiciaRepository;
use App\Models\Franquicia;
use Illuminate\Database\Eloquent\Collection;

class FranquiciaService
{
    protected FranquiciaRepository $franquiciaRepository;

    public function __construct(FranquiciaRepository $franquiciaRepository)
    {
        $this->franquiciaRepository = $franquiciaRepository;
    }

    public function getAllFranquicias(): Collection
    {
        return $this->franquiciaRepository->getAll();
    }

    public function getFranquiciaById(int $id): ?Franquicia
    {
        return $this->franquiciaRepository->findById($id);
    }

    public function crearFranquicia(array $data): array
    {
        try {
            $franquicia = $this->franquiciaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Franquicia creada exitosamente.',
                'data' => $franquicia
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la franquicia: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarFranquicia(int $id, array $data): array
    {
        try {
            $updated = $this->franquiciaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Franquicia no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Franquicia actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la franquicia: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarFranquicia(int $id): array
    {
        try {
            $deleted = $this->franquiciaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Franquicia no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Franquicia eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la franquicia: ' . $e->getMessage()
            ];
        }
    }
}

