<?php

namespace App\Repositories;

use App\Models\MarcFranUne;
use Illuminate\Database\Eloquent\Collection;

class MarcFranUneRepository
{
    /**
     * Obtiene todas las asignaciones marca-franquicia-une.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return MarcFranUne::with(['marca', 'franquicia', 'une', 'uneFranq'])->get();
    }

    /**
     * Obtiene una asignación marca-franquicia-une por su ID.
     *
     * @param int $id
     * @return MarcFranUne|null
     */
    public function findById(int $id): ?MarcFranUne
    {
        return MarcFranUne::with(['marca', 'franquicia', 'une', 'uneFranq'])->find($id);
    }

    /**
     * Crea una nueva asignación marca-franquicia-une.
     *
     * @param array $data
     * @return MarcFranUne
     */
    public function create(array $data): MarcFranUne
    {
        return MarcFranUne::create($data);
    }

    /**
     * Actualiza una asignación marca-franquicia-une existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $marcFranUne = $this->findById($id);
        
        if (!$marcFranUne) {
            return false;
        }

        return $marcFranUne->update($data);
    }

    /**
     * Elimina una asignación marca-franquicia-une.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $marcFranUne = $this->findById($id);
        
        if (!$marcFranUne) {
            return false;
        }

        return $marcFranUne->delete();
    }

    /**
     * Obtiene asignaciones por marca.
     *
     * @param int $idMarca
     * @return Collection
     */
    public function getPorMarca(int $idMarca): Collection
    {
        return MarcFranUne::porMarca($idMarca)->with(['marca', 'franquicia', 'une', 'uneFranq'])->get();
    }

    /**
     * Obtiene asignaciones por franquicia.
     *
     * @param int $idFranquicia
     * @return Collection
     */
    public function getPorFranquicia(int $idFranquicia): Collection
    {
        return MarcFranUne::porFranquicia($idFranquicia)->with(['marca', 'franquicia', 'une', 'uneFranq'])->get();
    }

    /**
     * Obtiene asignaciones por UNE.
     *
     * @param int $idUne
     * @return Collection
     */
    public function getPorUne(int $idUne): Collection
    {
        return MarcFranUne::porUne($idUne)->with(['marca', 'franquicia', 'une', 'uneFranq'])->get();
    }
}

