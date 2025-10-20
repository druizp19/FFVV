<?php

namespace App\Repositories;

use App\Models\Marca;
use Illuminate\Database\Eloquent\Collection;

class MarcaRepository
{
    /**
     * Obtiene todas las marcas.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Marca::with(['estado', 'franquicia'])->get();
    }

    /**
     * Obtiene una marca por su ID.
     *
     * @param int $id
     * @return Marca|null
     */
    public function findById(int $id): ?Marca
    {
        return Marca::with(['estado', 'franquicia'])->find($id);
    }

    /**
     * Crea una nueva marca.
     *
     * @param array $data
     * @return Marca
     */
    public function create(array $data): Marca
    {
        return Marca::create($data);
    }

    /**
     * Actualiza una marca existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $marca = $this->findById($id);
        
        if (!$marca) {
            return false;
        }

        return $marca->update($data);
    }

    /**
     * Elimina una marca.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $marca = $this->findById($id);
        
        if (!$marca) {
            return false;
        }

        return $marca->delete();
    }

    /**
     * Obtiene las marcas activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Marca::activas()->with(['estado', 'franquicia'])->get();
    }

    /**
     * Obtiene marcas por franquicia.
     *
     * @param int $idFranquicia
     * @return Collection
     */
    public function getPorFranquicia(int $idFranquicia): Collection
    {
        return Marca::porFranquicia($idFranquicia)->with(['estado', 'franquicia'])->get();
    }
}

