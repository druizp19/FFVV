<?php

namespace App\Repositories;

use App\Models\Zona;
use Illuminate\Database\Eloquent\Collection;

class ZonaRepository
{
    /**
     * Obtiene todas las zonas.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Zona::with('estado')->get();
    }

    /**
     * Obtiene una zona por su ID.
     *
     * @param int $id
     * @return Zona|null
     */
    public function findById(int $id): ?Zona
    {
        return Zona::with('estado')->find($id);
    }

    /**
     * Crea una nueva zona.
     *
     * @param array $data
     * @return Zona
     */
    public function create(array $data): Zona
    {
        return Zona::create($data);
    }

    /**
     * Actualiza una zona existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $zona = $this->findById($id);
        
        if (!$zona) {
            return false;
        }

        return $zona->update($data);
    }

    /**
     * Elimina una zona.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $zona = $this->findById($id);
        
        if (!$zona) {
            return false;
        }

        return $zona->delete();
    }

    /**
     * Obtiene las zonas activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Zona::activas()->with('estado')->get();
    }
}

