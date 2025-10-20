<?php

namespace App\Repositories;

use App\Models\Franquicia;
use Illuminate\Database\Eloquent\Collection;

class FranquiciaRepository
{
    /**
     * Obtiene todas las franquicias.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Franquicia::with('estado')->get();
    }

    /**
     * Obtiene una franquicia por su ID.
     *
     * @param int $id
     * @return Franquicia|null
     */
    public function findById(int $id): ?Franquicia
    {
        return Franquicia::with('estado')->find($id);
    }

    /**
     * Crea una nueva franquicia.
     *
     * @param array $data
     * @return Franquicia
     */
    public function create(array $data): Franquicia
    {
        return Franquicia::create($data);
    }

    /**
     * Actualiza una franquicia existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $franquicia = $this->findById($id);
        
        if (!$franquicia) {
            return false;
        }

        return $franquicia->update($data);
    }

    /**
     * Elimina una franquicia.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $franquicia = $this->findById($id);
        
        if (!$franquicia) {
            return false;
        }

        return $franquicia->delete();
    }

    /**
     * Obtiene las franquicias activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Franquicia::activas()->with('estado')->get();
    }
}

