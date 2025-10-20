<?php

namespace App\Repositories;

use App\Models\Ciclo;
use Illuminate\Database\Eloquent\Collection;

class CicloRepository
{
    /**
     * Obtiene todos los ciclos ordenados por fecha de inicio descendente.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Ciclo::orderBy('fechaInicio', 'desc')->get();
    }

    /**
     * Obtiene un ciclo por su ID.
     *
     * @param int $id
     * @return Ciclo|null
     */
    public function findById(int $id): ?Ciclo
    {
        return Ciclo::find($id);
    }

    /**
     * Crea un nuevo ciclo.
     *
     * @param array $data
     * @return Ciclo
     */
    public function create(array $data): Ciclo
    {
        return Ciclo::create($data);
    }

    /**
     * Actualiza un ciclo existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $ciclo = $this->findById($id);
        
        if (!$ciclo) {
            return false;
        }

        return $ciclo->update($data);
    }

    /**
     * Elimina un ciclo.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $ciclo = $this->findById($id);
        
        if (!$ciclo) {
            return false;
        }

        return $ciclo->delete();
    }

    /**
     * Obtiene los ciclos abiertos.
     *
     * @return Collection
     */
    public function getAbiertos(): Collection
    {
        return Ciclo::abiertos()->orderBy('fechaInicio', 'desc')->get();
    }

    /**
     * Obtiene los ciclos cerrados.
     *
     * @return Collection
     */
    public function getCerrados(): Collection
    {
        return Ciclo::cerrados()->orderBy('fechaInicio', 'desc')->get();
    }

    /**
     * Verifica si existe un ciclo con fechas solapadas.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int|null $exceptId
     * @return bool
     */
    public function existeSolapamiento(string $fechaInicio, string $fechaFin, ?int $exceptId = null): bool
    {
        $query = Ciclo::where(function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fechaInicio', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('fechaFin', [$fechaInicio, $fechaFin])
                  ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                      $q->where('fechaInicio', '<=', $fechaInicio)
                        ->where('fechaFin', '>=', $fechaFin);
                  });
        });

        if ($exceptId) {
            $query->where('idCiclo', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Obtiene un ciclo con todas sus relaciones cargadas.
     *
     * @param int $id
     * @return Ciclo|null
     */
    public function findByIdConRelaciones(int $id): ?Ciclo
    {
        return Ciclo::with([
            'productos',
            'zonasEmpleados',
            'zonasGeosegmentos',
            'fuerzasVenta'
        ])->find($id);
    }

    /**
     * Obtiene el último ciclo creado.
     *
     * @return Ciclo|null
     */
    public function getUltimoCiclo(): ?Ciclo
    {
        return Ciclo::orderBy('idCiclo', 'desc')->first();
    }
}


