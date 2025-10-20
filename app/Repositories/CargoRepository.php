<?php

namespace App\Repositories;

use App\Models\Cargo;
use Illuminate\Database\Eloquent\Collection;

class CargoRepository
{
    /**
     * Obtiene todos los cargos.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Cargo::with('estado')->get();
    }

    /**
     * Obtiene un cargo por su ID.
     *
     * @param int $id
     * @return Cargo|null
     */
    public function findById(int $id): ?Cargo
    {
        return Cargo::with('estado')->find($id);
    }

    /**
     * Crea un nuevo cargo.
     *
     * @param array $data
     * @return Cargo
     */
    public function create(array $data): Cargo
    {
        return Cargo::create($data);
    }

    /**
     * Actualiza un cargo existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $cargo = $this->findById($id);
        
        if (!$cargo) {
            return false;
        }

        return $cargo->update($data);
    }

    /**
     * Elimina un cargo.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $cargo = $this->findById($id);
        
        if (!$cargo) {
            return false;
        }

        return $cargo->delete();
    }

    /**
     * Obtiene los cargos activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Cargo::activos()->with('estado')->get();
    }
}

