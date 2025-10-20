<?php

namespace App\Repositories;

use App\Models\Mixta;
use Illuminate\Database\Eloquent\Collection;

class MixtaRepository
{
    /**
     * Obtiene todas las mixtas.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Mixta::with('estado')->get();
    }

    /**
     * Obtiene una mixta por su ID.
     *
     * @param int $id
     * @return Mixta|null
     */
    public function findById(int $id): ?Mixta
    {
        return Mixta::with('estado')->find($id);
    }

    /**
     * Crea una nueva mixta.
     *
     * @param array $data
     * @return Mixta
     */
    public function create(array $data): Mixta
    {
        return Mixta::create($data);
    }

    /**
     * Actualiza una mixta existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $mixta = $this->findById($id);
        
        if (!$mixta) {
            return false;
        }

        return $mixta->update($data);
    }

    /**
     * Elimina una mixta.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $mixta = $this->findById($id);
        
        if (!$mixta) {
            return false;
        }

        return $mixta->delete();
    }

    /**
     * Obtiene las mixtas activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Mixta::activas()->with('estado')->get();
    }
}

