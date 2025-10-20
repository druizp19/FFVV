<?php

namespace App\Repositories;

use App\Models\Promocion;
use Illuminate\Database\Eloquent\Collection;

class PromocionRepository
{
    /**
     * Obtiene todas las promociones.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Promocion::with('estado')->get();
    }

    /**
     * Obtiene una promoción por su ID.
     *
     * @param int $id
     * @return Promocion|null
     */
    public function findById(int $id): ?Promocion
    {
        return Promocion::with('estado')->find($id);
    }

    /**
     * Crea una nueva promoción.
     *
     * @param array $data
     * @return Promocion
     */
    public function create(array $data): Promocion
    {
        return Promocion::create($data);
    }

    /**
     * Actualiza una promoción existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $promocion = $this->findById($id);
        
        if (!$promocion) {
            return false;
        }

        return $promocion->update($data);
    }

    /**
     * Elimina una promoción.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $promocion = $this->findById($id);
        
        if (!$promocion) {
            return false;
        }

        return $promocion->delete();
    }

    /**
     * Obtiene las promociones activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Promocion::activas()->with('estado')->get();
    }
}

