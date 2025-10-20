<?php

namespace App\Repositories;

use App\Models\FranqLinea;
use Illuminate\Database\Eloquent\Collection;

class FranqLineaRepository
{
    /**
     * Obtiene todas las asignaciones franquicia-línea.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return FranqLinea::with(['franquicia', 'linea'])->get();
    }

    /**
     * Obtiene una asignación franquicia-línea por su ID.
     *
     * @param int $id
     * @return FranqLinea|null
     */
    public function findById(int $id): ?FranqLinea
    {
        return FranqLinea::with(['franquicia', 'linea'])->find($id);
    }

    /**
     * Crea una nueva asignación franquicia-línea.
     *
     * @param array $data
     * @return FranqLinea
     */
    public function create(array $data): FranqLinea
    {
        return FranqLinea::create($data);
    }

    /**
     * Actualiza una asignación franquicia-línea existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $franqLinea = $this->findById($id);
        
        if (!$franqLinea) {
            return false;
        }

        return $franqLinea->update($data);
    }

    /**
     * Elimina una asignación franquicia-línea.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $franqLinea = $this->findById($id);
        
        if (!$franqLinea) {
            return false;
        }

        return $franqLinea->delete();
    }
}

