<?php

namespace App\Repositories;

use App\Models\Linea;
use Illuminate\Database\Eloquent\Collection;

class LineaRepository
{
    /**
     * Obtiene todas las líneas.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Linea::with('estado')->get();
    }

    /**
     * Obtiene una línea por su ID.
     *
     * @param int $id
     * @return Linea|null
     */
    public function findById(int $id): ?Linea
    {
        return Linea::with('estado')->find($id);
    }

    /**
     * Crea una nueva línea.
     *
     * @param array $data
     * @return Linea
     */
    public function create(array $data): Linea
    {
        return Linea::create($data);
    }

    /**
     * Actualiza una línea existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $linea = $this->findById($id);
        
        if (!$linea) {
            return false;
        }

        return $linea->update($data);
    }

    /**
     * Elimina una línea.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $linea = $this->findById($id);
        
        if (!$linea) {
            return false;
        }

        return $linea->delete();
    }

    /**
     * Obtiene las líneas activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Linea::activas()->with('estado')->get();
    }
}

