<?php

namespace App\Repositories;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Collection;

class EstadoRepository
{
    /**
     * Obtiene todos los estados.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Estado::all();
    }

    /**
     * Obtiene un estado por su ID.
     *
     * @param int $id
     * @return Estado|null
     */
    public function findById(int $id): ?Estado
    {
        return Estado::find($id);
    }

    /**
     * Crea un nuevo estado.
     *
     * @param array $data
     * @return Estado
     */
    public function create(array $data): Estado
    {
        return Estado::create($data);
    }

    /**
     * Actualiza un estado existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $estado = $this->findById($id);
        
        if (!$estado) {
            return false;
        }

        return $estado->update($data);
    }

    /**
     * Elimina un estado.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $estado = $this->findById($id);
        
        if (!$estado) {
            return false;
        }

        return $estado->delete();
    }

    /**
     * Obtiene los estados activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Estado::activo()->get();
    }

    /**
     * Obtiene los estados inactivos.
     *
     * @return Collection
     */
    public function getInactivos(): Collection
    {
        return Estado::inactivo()->get();
    }
}

