<?php

namespace App\Repositories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Collection;

class EmpleadoRepository
{
    /**
     * Obtiene todos los empleados.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Empleado::with(['cargo', 'area', 'une', 'estado'])->get();
    }

    /**
     * Obtiene un empleado por su ID.
     *
     * @param int $id
     * @return Empleado|null
     */
    public function findById(int $id): ?Empleado
    {
        return Empleado::with(['cargo', 'area', 'une', 'estado'])->find($id);
    }

    /**
     * Crea un nuevo empleado.
     *
     * @param array $data
     * @return Empleado
     */
    public function create(array $data): Empleado
    {
        return Empleado::create($data);
    }

    /**
     * Actualiza un empleado existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $empleado = $this->findById($id);
        
        if (!$empleado) {
            return false;
        }

        return $empleado->update($data);
    }

    /**
     * Elimina un empleado.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $empleado = $this->findById($id);
        
        if (!$empleado) {
            return false;
        }

        return $empleado->delete();
    }

    /**
     * Obtiene los empleados activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Empleado::activos()->with(['cargo', 'area', 'une', 'estado'])->get();
    }

    /**
     * Obtiene empleados sin fecha de cese.
     *
     * @return Collection
     */
    public function getSinCese(): Collection
    {
        return Empleado::sinCese()->with(['cargo', 'area', 'une', 'estado'])->get();
    }

    /**
     * Obtiene empleados por cargo.
     *
     * @param int $idCargo
     * @return Collection
     */
    public function getPorCargo(int $idCargo): Collection
    {
        return Empleado::porCargo($idCargo)->with(['cargo', 'area', 'une', 'estado'])->get();
    }

    /**
     * Busca empleado por DNI.
     *
     * @param string $dni
     * @return Empleado|null
     */
    public function findByDni(string $dni): ?Empleado
    {
        return Empleado::where('dni', $dni)->with(['cargo', 'area', 'une', 'estado'])->first();
    }

    /**
     * Busca empleados por nombre o apellido.
     *
     * @param string $termino
     * @return Collection
     */
    public function buscarPorNombre(string $termino): Collection
    {
        return Empleado::where('nombre', 'like', "%{$termino}%")
            ->orWhere('apeNombre', 'like', "%{$termino}%")
            ->with(['cargo', 'area', 'une', 'estado'])
            ->get();
    }
}

