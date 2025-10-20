<?php

namespace App\Repositories;

use App\Models\Geosegmento;
use Illuminate\Database\Eloquent\Collection;

class GeosegmentoRepository
{
    /**
     * Obtiene todos los geosegmentos.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Geosegmento::with('estado')->get();
    }

    /**
     * Obtiene un geosegmento por su ID.
     *
     * @param int $id
     * @return Geosegmento|null
     */
    public function findById(int $id): ?Geosegmento
    {
        return Geosegmento::with('estado')->find($id);
    }

    /**
     * Crea un nuevo geosegmento.
     *
     * @param array $data
     * @return Geosegmento
     */
    public function create(array $data): Geosegmento
    {
        return Geosegmento::create($data);
    }

    /**
     * Actualiza un geosegmento existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $geosegmento = $this->findById($id);
        
        if (!$geosegmento) {
            return false;
        }

        return $geosegmento->update($data);
    }

    /**
     * Elimina un geosegmento.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $geosegmento = $this->findById($id);
        
        if (!$geosegmento) {
            return false;
        }

        return $geosegmento->delete();
    }

    /**
     * Obtiene los geosegmentos activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Geosegmento::activos()->with('estado')->get();
    }

    /**
     * Obtiene geosegmentos por lugar.
     *
     * @param string $lugar
     * @return Collection
     */
    public function getPorLugar(string $lugar): Collection
    {
        return Geosegmento::porLugar($lugar)->with('estado')->get();
    }
}

