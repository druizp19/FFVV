<?php

namespace App\Services;

use App\Repositories\CuotaRepository;
use App\Models\Cuota;
use Illuminate\Database\Eloquent\Collection;

class CuotaService
{
    protected CuotaRepository $cuotaRepository;

    public function __construct(CuotaRepository $cuotaRepository)
    {
        $this->cuotaRepository = $cuotaRepository;
    }

    public function getAllCuotas(): Collection
    {
        return $this->cuotaRepository->getAll();
    }

    public function getCuotaById(int $id): ?Cuota
    {
        return $this->cuotaRepository->findById($id);
    }

    public function crearCuota(array $data): array
    {
        try {
            $cuota = $this->cuotaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Cuota creada exitosamente.',
                'data' => $cuota
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la cuota: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarCuota(int $id, array $data): array
    {
        try {
            $updated = $this->cuotaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Cuota no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Cuota actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la cuota: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarCuota(int $id): array
    {
        try {
            $deleted = $this->cuotaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Cuota no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Cuota eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la cuota: ' . $e->getMessage()
            ];
        }
    }
}

