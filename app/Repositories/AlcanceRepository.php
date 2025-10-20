<?php

namespace App\Repositories;

use App\Models\Alcance;
use Illuminate\Database\Eloquent\Collection;

class AlcanceRepository
{
    /**
     * Obtiene todos los alcances.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Alcance::with('estado')->get();
    }

    /**
     * Obtiene un alcance por su ID.
     *
     * @param int $id
     * @return Alcance|null
     */
    public function findById(int $id): ?Alcance
    {
        return Alcance::with('estado')->find($id);
    }

    /**
     * Crea un nuevo alcance.
     *
     * @param array $data
     * @return Alcance
     */
    public function create(array $data): Alcance
    {
        return Alcance::create($data);
    }

    /**
     * Actualiza un alcance existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $alcance = $this->findById($id);
        
        if (!$alcance) {
            return false;
        }

        return $alcance->update($data);
    }

    /**
     * Elimina un alcance.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $alcance = $this->findById($id);
        
        if (!$alcance) {
            return false;
        }

        return $alcance->delete();
    }

    /**
     * Obtiene los alcances activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Alcance::activos()->with('estado')->get();
    }
}

