<?php

namespace App\Repositories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;

class AreaRepository
{
    /**
     * Obtiene todas las áreas.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Area::with('estado')->get();
    }

    /**
     * Obtiene un área por su ID.
     *
     * @param int $id
     * @return Area|null
     */
    public function findById(int $id): ?Area
    {
        return Area::with('estado')->find($id);
    }

    /**
     * Crea una nueva área.
     *
     * @param array $data
     * @return Area
     */
    public function create(array $data): Area
    {
        return Area::create($data);
    }

    /**
     * Actualiza un área existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $area = $this->findById($id);
        
        if (!$area) {
            return false;
        }

        return $area->update($data);
    }

    /**
     * Elimina un área.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $area = $this->findById($id);
        
        if (!$area) {
            return false;
        }

        return $area->delete();
    }

    /**
     * Obtiene las áreas activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Area::activas()->with('estado')->get();
    }
}

