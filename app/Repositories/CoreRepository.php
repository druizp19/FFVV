<?php

namespace App\Repositories;

use App\Models\Core;
use Illuminate\Database\Eloquent\Collection;

class CoreRepository
{
    /**
     * Obtiene todos los cores.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Core::with('estado')->get();
    }

    /**
     * Obtiene un core por su ID.
     *
     * @param int $id
     * @return Core|null
     */
    public function findById(int $id): ?Core
    {
        return Core::with('estado')->find($id);
    }

    /**
     * Crea un nuevo core.
     *
     * @param array $data
     * @return Core
     */
    public function create(array $data): Core
    {
        return Core::create($data);
    }

    /**
     * Actualiza un core existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $core = $this->findById($id);
        
        if (!$core) {
            return false;
        }

        return $core->update($data);
    }

    /**
     * Elimina un core.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $core = $this->findById($id);
        
        if (!$core) {
            return false;
        }

        return $core->delete();
    }

    /**
     * Obtiene los cores activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Core::activos()->with('estado')->get();
    }
}

