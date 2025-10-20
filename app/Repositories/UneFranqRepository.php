<?php

namespace App\Repositories;

use App\Models\UneFranq;
use Illuminate\Database\Eloquent\Collection;

class UneFranqRepository
{
    /**
     * Obtiene todas las asignaciones une-franquicia.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return UneFranq::with(['une', 'franquicia', 'empleado'])->get();
    }

    /**
     * Obtiene una asignación une-franquicia por su ID.
     *
     * @param int $id
     * @return UneFranq|null
     */
    public function findById(int $id): ?UneFranq
    {
        return UneFranq::with(['une', 'franquicia', 'empleado'])->find($id);
    }

    /**
     * Crea una nueva asignación une-franquicia.
     *
     * @param array $data
     * @return UneFranq
     */
    public function create(array $data): UneFranq
    {
        return UneFranq::create($data);
    }

    /**
     * Actualiza una asignación une-franquicia existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $uneFranq = $this->findById($id);
        
        if (!$uneFranq) {
            return false;
        }

        return $uneFranq->update($data);
    }

    /**
     * Elimina una asignación une-franquicia.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $uneFranq = $this->findById($id);
        
        if (!$uneFranq) {
            return false;
        }

        return $uneFranq->delete();
    }
}

