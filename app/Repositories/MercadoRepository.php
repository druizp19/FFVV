<?php

namespace App\Repositories;

use App\Models\Mercado;
use Illuminate\Database\Eloquent\Collection;

class MercadoRepository
{
    /**
     * Obtiene todos los mercados.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Mercado::with('estado')->get();
    }

    /**
     * Obtiene un mercado por su ID.
     *
     * @param int $id
     * @return Mercado|null
     */
    public function findById(int $id): ?Mercado
    {
        return Mercado::with('estado')->find($id);
    }

    /**
     * Crea un nuevo mercado.
     *
     * @param array $data
     * @return Mercado
     */
    public function create(array $data): Mercado
    {
        return Mercado::create($data);
    }

    /**
     * Actualiza un mercado existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $mercado = $this->findById($id);
        
        if (!$mercado) {
            return false;
        }

        return $mercado->update($data);
    }

    /**
     * Elimina un mercado.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $mercado = $this->findById($id);
        
        if (!$mercado) {
            return false;
        }

        return $mercado->delete();
    }

    /**
     * Obtiene los mercados activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Mercado::activos()->with('estado')->get();
    }
}

