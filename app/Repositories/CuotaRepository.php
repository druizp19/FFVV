<?php

namespace App\Repositories;

use App\Models\Cuota;
use Illuminate\Database\Eloquent\Collection;

class CuotaRepository
{
    /**
     * Obtiene todas las cuotas.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Cuota::with('estado')->get();
    }

    /**
     * Obtiene una cuota por su ID.
     *
     * @param int $id
     * @return Cuota|null
     */
    public function findById(int $id): ?Cuota
    {
        return Cuota::with('estado')->find($id);
    }

    /**
     * Crea una nueva cuota.
     *
     * @param array $data
     * @return Cuota
     */
    public function create(array $data): Cuota
    {
        return Cuota::create($data);
    }

    /**
     * Actualiza una cuota existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $cuota = $this->findById($id);
        
        if (!$cuota) {
            return false;
        }

        return $cuota->update($data);
    }

    /**
     * Elimina una cuota.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $cuota = $this->findById($id);
        
        if (!$cuota) {
            return false;
        }

        return $cuota->delete();
    }

    /**
     * Obtiene las cuotas activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return Cuota::activas()->with('estado')->get();
    }
}

