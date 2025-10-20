<?php

namespace App\Repositories;

use App\Models\MarcaMkt;
use Illuminate\Database\Eloquent\Collection;

class MarcaMktRepository
{
    /**
     * Obtiene todas las marcas-mercado.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return MarcaMkt::with(['marca', 'mercado', 'estado'])->get();
    }

    /**
     * Obtiene una marca-mercado por su ID.
     *
     * @param int $id
     * @return MarcaMkt|null
     */
    public function findById(int $id): ?MarcaMkt
    {
        return MarcaMkt::with(['marca', 'mercado', 'estado'])->find($id);
    }

    /**
     * Crea una nueva marca-mercado.
     *
     * @param array $data
     * @return MarcaMkt
     */
    public function create(array $data): MarcaMkt
    {
        return MarcaMkt::create($data);
    }

    /**
     * Actualiza una marca-mercado existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $marcaMkt = $this->findById($id);
        
        if (!$marcaMkt) {
            return false;
        }

        return $marcaMkt->update($data);
    }

    /**
     * Elimina una marca-mercado.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $marcaMkt = $this->findById($id);
        
        if (!$marcaMkt) {
            return false;
        }

        return $marcaMkt->delete();
    }

    /**
     * Obtiene las marcas-mercado activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return MarcaMkt::activas()->with(['marca', 'mercado', 'estado'])->get();
    }

    /**
     * Obtiene marcas-mercado por marca.
     *
     * @param int $idMarca
     * @return Collection
     */
    public function getPorMarca(int $idMarca): Collection
    {
        return MarcaMkt::porMarca($idMarca)->with(['marca', 'mercado', 'estado'])->get();
    }

    /**
     * Obtiene marcas-mercado por mercado.
     *
     * @param int $idMercado
     * @return Collection
     */
    public function getPorMercado(int $idMercado): Collection
    {
        return MarcaMkt::porMercado($idMercado)->with(['marca', 'mercado', 'estado'])->get();
    }
}

