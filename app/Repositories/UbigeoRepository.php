<?php

namespace App\Repositories;

use App\Models\Ubigeo;
use Illuminate\Database\Eloquent\Collection;

class UbigeoRepository
{
    /**
     * Obtiene todos los ubigeos.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Ubigeo::with(['geosegmento', 'estado'])->get();
    }

    /**
     * Obtiene un ubigeo por su ID.
     *
     * @param int $id
     * @return Ubigeo|null
     */
    public function findById(int $id): ?Ubigeo
    {
        return Ubigeo::with(['geosegmento', 'estado'])->find($id);
    }

    /**
     * Crea un nuevo ubigeo.
     *
     * @param array $data
     * @return Ubigeo
     */
    public function create(array $data): Ubigeo
    {
        return Ubigeo::create($data);
    }

    /**
     * Actualiza un ubigeo existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $ubigeo = Ubigeo::find($id);
        
        if (!$ubigeo) {
            return false;
        }

        return $ubigeo->update($data);
    }

    /**
     * Elimina un ubigeo.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $ubigeo = Ubigeo::find($id);
        
        if (!$ubigeo) {
            return false;
        }

        return $ubigeo->delete();
    }

    /**
     * Obtiene ubigeos activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Ubigeo::activos()
            ->with(['geosegmento', 'estado'])
            ->orderBy('departamento')
            ->orderBy('provincia')
            ->orderBy('distrito')
            ->get();
    }

    /**
     * Obtiene ubigeos por departamento.
     *
     * @param string $departamento
     * @return Collection
     */
    public function getPorDepartamento(string $departamento): Collection
    {
        return Ubigeo::porDepartamento($departamento)
            ->with(['geosegmento', 'estado'])
            ->orderBy('provincia')
            ->orderBy('distrito')
            ->get();
    }

    /**
     * Obtiene ubigeos por provincia.
     *
     * @param string $provincia
     * @return Collection
     */
    public function getPorProvincia(string $provincia): Collection
    {
        return Ubigeo::porProvincia($provincia)
            ->with(['geosegmento', 'estado'])
            ->orderBy('distrito')
            ->get();
    }

    /**
     * Obtiene ubigeos por distrito.
     *
     * @param string $distrito
     * @return Collection
     */
    public function getPorDistrito(string $distrito): Collection
    {
        return Ubigeo::porDistrito($distrito)
            ->with(['geosegmento', 'estado'])
            ->get();
    }

    /**
     * Obtiene ubigeos por región.
     *
     * @param string $region
     * @return Collection
     */
    public function getPorRegion(string $region): Collection
    {
        return Ubigeo::porRegion($region)
            ->with(['geosegmento', 'estado'])
            ->orderBy('departamento')
            ->orderBy('provincia')
            ->orderBy('distrito')
            ->get();
    }

    /**
     * Obtiene ubigeos por geosegmento.
     *
     * @param int $idGeosegmento
     * @return Collection
     */
    public function getPorGeosegmento(int $idGeosegmento): Collection
    {
        return Ubigeo::porGeosegmento($idGeosegmento)
            ->with(['geosegmento', 'estado'])
            ->orderBy('departamento')
            ->orderBy('provincia')
            ->orderBy('distrito')
            ->get();
    }

    /**
     * Busca ubigeos por término de búsqueda (departamento, provincia, distrito).
     *
     * @param string $term
     * @return Collection
     */
    public function buscar(string $term): Collection
    {
        return Ubigeo::with(['geosegmento', 'estado'])
            ->where(function ($query) use ($term) {
                $query->where('departamento', 'like', "%{$term}%")
                    ->orWhere('provincia', 'like', "%{$term}%")
                    ->orWhere('distrito', 'like', "%{$term}%")
                    ->orWhere('ubigeo', 'like', "%{$term}%");
            })
            ->orderBy('departamento')
            ->orderBy('provincia')
            ->orderBy('distrito')
            ->get();
    }

    /**
     * Obtiene departamentos únicos.
     *
     * @return Collection
     */
    public function getDepartamentos(): Collection
    {
        return Ubigeo::select('departamento')
            ->distinct()
            ->whereNotNull('departamento')
            ->orderBy('departamento')
            ->pluck('departamento');
    }

    /**
     * Obtiene provincias por departamento.
     *
     * @param string $departamento
     * @return Collection
     */
    public function getProvinciasPorDepartamento(string $departamento): Collection
    {
        return Ubigeo::select('provincia')
            ->where('departamento', $departamento)
            ->distinct()
            ->whereNotNull('provincia')
            ->orderBy('provincia')
            ->pluck('provincia');
    }

    /**
     * Obtiene distritos por provincia.
     *
     * @param string $provincia
     * @return Collection
     */
    public function getDistritosPorProvincia(string $provincia): Collection
    {
        return Ubigeo::select('distrito')
            ->where('provincia', $provincia)
            ->distinct()
            ->whereNotNull('distrito')
            ->orderBy('distrito')
            ->pluck('distrito');
    }
}

