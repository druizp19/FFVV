<?php

namespace App\Repositories;

use App\Models\Estructura;
use Illuminate\Database\Eloquent\Collection;

class EstructuraRepository
{
    /**
     * Obtiene todas las estructuras.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Estructura::with('estado')->get();
    }

    /**
     * Obtiene una estructura por su ID.
     *
     * @param int $id
     * @return Estructura|null
     */
    public function findById(int $id): ?Estructura
    {
        return Estructura::with('estado')->find($id);
    }

    /**
     * Crea una nueva estructura.
     *
     * @param array $data
     * @return Estructura
     */
    public function create(array $data): Estructura
    {
        return Estructura::create($data);
    }

    /**
     * Actualiza una estructura existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $estructura = $this->findById($id);
        
        if (!$estructura) {
            return false;
        }

        return $estructura->update($data);
    }

    /**
     * Elimina una estructura.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $estructura = $this->findById($id);
        
        if (!$estructura) {
            return false;
        }

        return $estructura->delete();
    }

    /**
     * Obtiene las estructuras activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Estructura::activas()->with('estado')->get();
    }
}

