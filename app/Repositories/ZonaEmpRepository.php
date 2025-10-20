<?php

namespace App\Repositories;

use App\Models\ZonaEmp;
use Illuminate\Database\Eloquent\Collection;

class ZonaEmpRepository
{
    /**
     * Obtiene todas las asignaciones zona-empleado.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return ZonaEmp::with(['ciclo', 'zona', 'empleado', 'estado'])->get();
    }

    /**
     * Obtiene una asignación zona-empleado por su ID.
     *
     * @param int $id
     * @return ZonaEmp|null
     */
    public function findById(int $id): ?ZonaEmp
    {
        return ZonaEmp::with(['ciclo', 'zona', 'empleado', 'estado'])->find($id);
    }

    /**
     * Crea una nueva asignación zona-empleado.
     *
     * @param array $data
     * @return ZonaEmp
     */
    public function create(array $data): ZonaEmp
    {
        return ZonaEmp::create($data);
    }

    /**
     * Actualiza una asignación zona-empleado existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $zonaEmp = $this->findById($id);
        
        if (!$zonaEmp) {
            return false;
        }

        return $zonaEmp->update($data);
    }

    /**
     * Elimina una asignación zona-empleado.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $zonaEmp = $this->findById($id);
        
        if (!$zonaEmp) {
            return false;
        }

        return $zonaEmp->delete();
    }

    /**
     * Obtiene las asignaciones activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return ZonaEmp::activas()->with(['ciclo', 'zona', 'empleado', 'estado'])->get();
    }

    /**
     * Obtiene asignaciones por ciclo.
     *
     * @param int $idCiclo
     * @return Collection
     */
    public function getPorCiclo(int $idCiclo): Collection
    {
        return ZonaEmp::porCiclo($idCiclo)->with(['ciclo', 'zona', 'empleado', 'estado'])->get();
    }
}

