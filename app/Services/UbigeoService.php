<?php

namespace App\Services;

use App\Repositories\UbigeoRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

class UbigeoService
{
    protected UbigeoRepository $ubigeoRepository;

    public function __construct(UbigeoRepository $ubigeoRepository)
    {
        $this->ubigeoRepository = $ubigeoRepository;
    }

    /**
     * Obtiene todos los ubigeos.
     *
     * @return Collection
     */
    public function getAllUbigeos(): Collection
    {
        return $this->ubigeoRepository->getAll();
    }

    /**
     * Obtiene un ubigeo por su ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getUbigeoById(int $id)
    {
        return $this->ubigeoRepository->findById($id);
    }

    /**
     * Crea un nuevo ubigeo.
     *
     * @param array $data
     * @return array
     */
    public function crearUbigeo(array $data): array
    {
        try {
            $ubigeo = $this->ubigeoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Ubigeo creado exitosamente.',
                'data' => $ubigeo
            ];
        } catch (Exception $e) {
            Log::error('Error al crear ubigeo: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al crear el ubigeo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un ubigeo existente.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function actualizarUbigeo(int $id, array $data): array
    {
        try {
            $ubigeo = $this->ubigeoRepository->findById($id);

            if (!$ubigeo) {
                return [
                    'success' => false,
                    'message' => 'Ubigeo no encontrado.'
                ];
            }

            $this->ubigeoRepository->update($id, $data);

            return [
                'success' => true,
                'message' => 'Ubigeo actualizado exitosamente.',
                'data' => $this->ubigeoRepository->findById($id)
            ];
        } catch (Exception $e) {
            Log::error('Error al actualizar ubigeo: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al actualizar el ubigeo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un ubigeo.
     *
     * @param int $id
     * @return array
     */
    public function eliminarUbigeo(int $id): array
    {
        try {
            $ubigeo = $this->ubigeoRepository->findById($id);

            if (!$ubigeo) {
                return [
                    'success' => false,
                    'message' => 'Ubigeo no encontrado.'
                ];
            }

            $this->ubigeoRepository->delete($id);

            return [
                'success' => true,
                'message' => 'Ubigeo eliminado exitosamente.'
            ];
        } catch (Exception $e) {
            Log::error('Error al eliminar ubigeo: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al eliminar el ubigeo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene ubigeos activos.
     *
     * @return Collection
     */
    public function getUbigeosActivos(): Collection
    {
        return $this->ubigeoRepository->getActivos();
    }

    /**
     * Obtiene ubigeos por departamento.
     *
     * @param string $departamento
     * @return Collection
     */
    public function getUbigeosPorDepartamento(string $departamento): Collection
    {
        return $this->ubigeoRepository->getPorDepartamento($departamento);
    }

    /**
     * Obtiene ubigeos por provincia.
     *
     * @param string $provincia
     * @return Collection
     */
    public function getUbigeosPorProvincia(string $provincia): Collection
    {
        return $this->ubigeoRepository->getPorProvincia($provincia);
    }

    /**
     * Obtiene ubigeos por distrito.
     *
     * @param string $distrito
     * @return Collection
     */
    public function getUbigeosPorDistrito(string $distrito): Collection
    {
        return $this->ubigeoRepository->getPorDistrito($distrito);
    }

    /**
     * Obtiene ubigeos por región.
     *
     * @param string $region
     * @return Collection
     */
    public function getUbigeosPorRegion(string $region): Collection
    {
        return $this->ubigeoRepository->getPorRegion($region);
    }

    /**
     * Obtiene ubigeos por geosegmento.
     *
     * @param int $idGeosegmento
     * @return Collection
     */
    public function getUbigeosPorGeosegmento(int $idGeosegmento): Collection
    {
        return $this->ubigeoRepository->getPorGeosegmento($idGeosegmento);
    }

    /**
     * Busca ubigeos por término.
     *
     * @param string $term
     * @return Collection
     */
    public function buscarUbigeos(string $term): Collection
    {
        return $this->ubigeoRepository->buscar($term);
    }

    /**
     * Obtiene departamentos únicos.
     *
     * @return Collection
     */
    public function getDepartamentos(): Collection
    {
        return $this->ubigeoRepository->getDepartamentos();
    }

    /**
     * Obtiene provincias por departamento.
     *
     * @param string $departamento
     * @return Collection
     */
    public function getProvinciasPorDepartamento(string $departamento): Collection
    {
        return $this->ubigeoRepository->getProvinciasPorDepartamento($departamento);
    }

    /**
     * Obtiene distritos por provincia.
     *
     * @param string $provincia
     * @return Collection
     */
    public function getDistritosPorProvincia(string $provincia): Collection
    {
        return $this->ubigeoRepository->getDistritosPorProvincia($provincia);
    }
}

