<?php

namespace App\Services;

use App\Repositories\HistorialRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HistorialService
{
    protected HistorialRepository $historialRepository;

    public function __construct(HistorialRepository $historialRepository)
    {
        $this->historialRepository = $historialRepository;
    }

    /**
     * Obtiene el historial con filtros y paginación.
     *
     * @param array $filtros
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getHistorial(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->historialRepository->getAllPaginated($filtros, $perPage);
    }

    /**
     * Obtiene el historial por ciclo.
     *
     * @param int $idCiclo
     * @return Collection
     */
    public function getHistorialPorCiclo(int $idCiclo): Collection
    {
        return $this->historialRepository->getPorCiclo($idCiclo);
    }

    /**
     * Obtiene el historial de una entidad específica.
     *
     * @param string $entidad
     * @param int $idEntidad
     * @return Collection
     */
    public function getHistorialPorEntidad(string $entidad, int $idEntidad): Collection
    {
        return $this->historialRepository->getPorEntidad($entidad, $idEntidad);
    }

    /**
     * Registra un evento en el historial.
     *
     * @param string $entidad
     * @param string $accion
     * @param string $descripcion
     * @param array $opciones
     * @return array
     */
    public function registrarEvento(
        string $entidad,
        string $accion,
        string $descripcion,
        array $opciones = []
    ): array {
        try {
            $historial = $this->historialRepository->registrar([
                'entidad' => $entidad,
                'accion' => $accion,
                'descripcion' => $descripcion,
                'idCiclo' => $opciones['idCiclo'] ?? null,
                'idEntidad' => $opciones['idEntidad'] ?? null,
                'datosAnteriores' => $opciones['datosAnteriores'] ?? null,
                'datosNuevos' => $opciones['datosNuevos'] ?? null,
                'idUsuario' => $opciones['idUsuario'] ?? auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'Evento registrado en el historial',
                'data' => $historial
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al registrar el evento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene estadísticas del historial por ciclo.
     *
     * @param int $idCiclo
     * @return array
     */
    public function getEstadisticasPorCiclo(int $idCiclo): array
    {
        return $this->historialRepository->getEstadisticasPorCiclo($idCiclo);
    }

    /**
     * Obtiene las entidades disponibles para filtrar.
     *
     * @return Collection
     */
    public function getEntidadesDisponibles(): Collection
    {
        return $this->historialRepository->getEntidadesUnicas();
    }

    /**
     * Obtiene las acciones disponibles para filtrar.
     *
     * @return Collection
     */
    public function getAccionesDisponibles(): Collection
    {
        return $this->historialRepository->getAccionesUnicas();
    }
}
