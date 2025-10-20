<?php

namespace App\Services;

use App\Repositories\FranqLineaRepository;
use App\Models\FranqLinea;
use Illuminate\Database\Eloquent\Collection;

class FranqLineaService
{
    protected FranqLineaRepository $franqLineaRepository;

    public function __construct(FranqLineaRepository $franqLineaRepository)
    {
        $this->franqLineaRepository = $franqLineaRepository;
    }

    public function getAllFranquiciasLineas(): Collection
    {
        return $this->franqLineaRepository->getAll();
    }

    public function getFranqLineaById(int $id): ?FranqLinea
    {
        return $this->franqLineaRepository->findById($id);
    }

    public function crearFranqLinea(array $data): array
    {
        try {
            $franqLinea = $this->franqLineaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Asignación Franquicia-Línea creada exitosamente.',
                'data' => $franqLinea
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarFranqLinea(int $id, array $data): array
    {
        try {
            $updated = $this->franqLineaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Asignación Franquicia-Línea no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Franquicia-Línea actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarFranqLinea(int $id): array
    {
        try {
            $deleted = $this->franqLineaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Asignación Franquicia-Línea no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Franquicia-Línea eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ];
        }
    }
}

