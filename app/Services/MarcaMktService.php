<?php

namespace App\Services;

use App\Repositories\MarcaMktRepository;
use App\Models\MarcaMkt;
use Illuminate\Database\Eloquent\Collection;

class MarcaMktService
{
    protected MarcaMktRepository $marcaMktRepository;

    public function __construct(MarcaMktRepository $marcaMktRepository)
    {
        $this->marcaMktRepository = $marcaMktRepository;
    }

    public function getAllMarcasMercados(): Collection
    {
        return $this->marcaMktRepository->getAll();
    }

    public function getMarcaMktById(int $id): ?MarcaMkt
    {
        return $this->marcaMktRepository->findById($id);
    }

    public function crearMarcaMkt(array $data): array
    {
        try {
            $marcaMkt = $this->marcaMktRepository->create($data);

            return [
                'success' => true,
                'message' => 'Marca-Mercado creada exitosamente.',
                'data' => $marcaMkt
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la marca-mercado: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarMarcaMkt(int $id, array $data): array
    {
        try {
            $updated = $this->marcaMktRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Marca-Mercado no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Marca-Mercado actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la marca-mercado: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarMarcaMkt(int $id): array
    {
        try {
            $deleted = $this->marcaMktRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Marca-Mercado no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Marca-Mercado eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la marca-mercado: ' . $e->getMessage()
            ];
        }
    }
}

