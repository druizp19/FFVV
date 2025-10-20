<?php

namespace App\Services;

use App\Repositories\LineaRepository;
use App\Models\Linea;
use Illuminate\Database\Eloquent\Collection;

class LineaService
{
    protected LineaRepository $lineaRepository;

    public function __construct(LineaRepository $lineaRepository)
    {
        $this->lineaRepository = $lineaRepository;
    }

    public function getAllLineas(): Collection
    {
        return $this->lineaRepository->getAll();
    }

    public function getLineaById(int $id): ?Linea
    {
        return $this->lineaRepository->findById($id);
    }

    public function crearLinea(array $data): array
    {
        try {
            $linea = $this->lineaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Línea creada exitosamente.',
                'data' => $linea
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la línea: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarLinea(int $id, array $data): array
    {
        try {
            $updated = $this->lineaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Línea no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Línea actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la línea: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarLinea(int $id): array
    {
        try {
            $deleted = $this->lineaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Línea no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Línea eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la línea: ' . $e->getMessage()
            ];
        }
    }
}

