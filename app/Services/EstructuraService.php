<?php

namespace App\Services;

use App\Repositories\EstructuraRepository;
use App\Models\Estructura;
use Illuminate\Database\Eloquent\Collection;

class EstructuraService
{
    protected EstructuraRepository $estructuraRepository;

    public function __construct(EstructuraRepository $estructuraRepository)
    {
        $this->estructuraRepository = $estructuraRepository;
    }

    public function getAllEstructuras(): Collection
    {
        return $this->estructuraRepository->getAll();
    }

    public function getEstructuraById(int $id): ?Estructura
    {
        return $this->estructuraRepository->findById($id);
    }

    public function crearEstructura(array $data): array
    {
        try {
            $estructura = $this->estructuraRepository->create($data);

            return [
                'success' => true,
                'message' => 'Estructura creada exitosamente.',
                'data' => $estructura
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la estructura: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarEstructura(int $id, array $data): array
    {
        try {
            $updated = $this->estructuraRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Estructura no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Estructura actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la estructura: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarEstructura(int $id): array
    {
        try {
            $deleted = $this->estructuraRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Estructura no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Estructura eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la estructura: ' . $e->getMessage()
            ];
        }
    }
}

