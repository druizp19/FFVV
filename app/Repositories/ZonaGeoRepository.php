<?php

namespace App\Repositories;

use App\Models\ZonaGeo;
use Illuminate\Database\Eloquent\Collection;

class ZonaGeoRepository
{
    /**
     * Obtiene todas las asignaciones zona-geosegmento.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return ZonaGeo::with(['zona', 'geosegmento', 'estado', 'ciclo'])->get();
    }

    /**
     * Obtiene una asignación zona-geosegmento por su ID.
     *
     * @param int $id
     * @return ZonaGeo|null
     */
    public function findById(int $id): ?ZonaGeo
    {
        return ZonaGeo::with(['zona', 'geosegmento', 'estado', 'ciclo'])->find($id);
    }

    /**
     * Crea una nueva asignación zona-geosegmento.
     *
     * @param array $data
     * @return ZonaGeo
     */
    public function create(array $data): ZonaGeo
    {
        return ZonaGeo::create($data);
    }

    /**
     * Actualiza una asignación zona-geosegmento existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $zonaGeo = $this->findById($id);
        
        if (!$zonaGeo) {
            return false;
        }

        return $zonaGeo->update($data);
    }

    /**
     * Elimina una asignación zona-geosegmento.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $zonaGeo = $this->findById($id);
        
        if (!$zonaGeo) {
            return false;
        }

        return $zonaGeo->delete();
    }

    /**
     * Obtiene las asignaciones activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return ZonaGeo::activas()->with(['zona', 'geosegmento', 'estado', 'ciclo'])->get();
    }

    /**
     * Obtiene asignaciones por ciclo.
     *
     * @param int $idCiclo
     * @return Collection
     */
    public function getPorCiclo(int $idCiclo): Collection
    {
        return ZonaGeo::porCiclo($idCiclo)->with(['zona', 'geosegmento', 'estado', 'ciclo'])->get();
    }
}

