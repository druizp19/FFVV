<?php

namespace App\Repositories;

use App\Models\Une;
use Illuminate\Database\Eloquent\Collection;

class UneRepository
{
    /**
     * Obtiene todas las unidades de negocio.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Une::with('estado')->get();
    }

    /**
     * Obtiene una unidad de negocio por su ID.
     *
     * @param int $id
     * @return Une|null
     */
    public function findById(int $id): ?Une
    {
        return Une::with('estado')->find($id);
    }

    /**
     * Crea una nueva unidad de negocio.
     *
     * @param array $data
     * @return Une
     */
    public function create(array $data): Une
    {
        return Une::create($data);
    }

    /**
     * Actualiza una unidad de negocio existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $une = $this->findById($id);
        
        if (!$une) {
            return false;
        }

        return $une->update($data);
    }

    /**
     * Elimina una unidad de negocio.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $une = $this->findById($id);
        
        if (!$une) {
            return false;
        }

        return $une->delete();
    }

    /**
     * Obtiene las unidades de negocio activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Une::activas()->with('estado')->get();
    }
}

