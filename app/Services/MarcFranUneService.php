<?php

namespace App\Services;

use App\Repositories\MarcFranUneRepository;
use App\Models\MarcFranUne;
use Illuminate\Database\Eloquent\Collection;

class MarcFranUneService
{
    protected MarcFranUneRepository $marcFranUneRepository;

    public function __construct(MarcFranUneRepository $marcFranUneRepository)
    {
        $this->marcFranUneRepository = $marcFranUneRepository;
    }

    public function getAllMarcasFranquiciasUnes(): Collection
    {
        return $this->marcFranUneRepository->getAll();
    }

    public function getMarcFranUneById(int $id): ?MarcFranUne
    {
        return $this->marcFranUneRepository->findById($id);
    }

    public function crearMarcFranUne(array $data): array
    {
        try {
            $marcFranUne = $this->marcFranUneRepository->create($data);

            return [
                'success' => true,
                'message' => 'Asignación Marca-Franquicia-UNE creada exitosamente.',
                'data' => $marcFranUne
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarMarcFranUne(int $id, array $data): array
    {
        try {
            $updated = $this->marcFranUneRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Asignación Marca-Franquicia-UNE no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Marca-Franquicia-UNE actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la asignación: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarMarcFranUne(int $id): array
    {
        try {
            $deleted = $this->marcFranUneRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Asignación Marca-Franquicia-UNE no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asignación Marca-Franquicia-UNE eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ];
        }
    }
}

