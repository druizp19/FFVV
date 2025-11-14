<?php

namespace App\Repositories;

use App\Models\Historial;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HistorialRepository
{
    /**
     * Obtiene todos los registros de historial con paginación.
     *
     * @param array $filtros
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $query = Historial::with(['ciclo']);

        // Aplicar filtros
        if (!empty($filtros['ciclo'])) {
            $query->where('idCiclo', $filtros['ciclo']);
        }

        if (!empty($filtros['entidad'])) {
            $query->where('entidad', $filtros['entidad']);
        }

        if (!empty($filtros['accion'])) {
            $query->where('accion', $filtros['accion']);
        }

        if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
            $query->whereBetween('fechaHora', [$filtros['desde'], $filtros['hasta']]);
        }

        if (!empty($filtros['search'])) {
            $query->where(function($q) use ($filtros) {
                $q->where('descripcion', 'like', '%' . $filtros['search'] . '%')
                  ->orWhere('entidad', 'like', '%' . $filtros['search'] . '%');
            });
        }

        return $query->orderBy('fechaHora', 'desc')->paginate($perPage);
    }

    /**
     * Obtiene el historial por ciclo.
     *
     * @param int $idCiclo
     * @return Collection
     */
    public function getPorCiclo(int $idCiclo): Collection
    {
        return Historial::with(['ciclo'])
            ->where('idCiclo', $idCiclo)
            ->orderBy('fechaHora', 'desc')
            ->get();
    }

    /**
     * Obtiene el historial por entidad.
     *
     * @param string $entidad
     * @param int $idEntidad
     * @return Collection
     */
    public function getPorEntidad(string $entidad, int $idEntidad): Collection
    {
        return Historial::with(['ciclo'])
            ->where('entidad', $entidad)
            ->where('idEntidad', $idEntidad)
            ->orderBy('fechaHora', 'desc')
            ->get();
    }

    /**
     * Registra un nuevo evento en el historial.
     *
     * @param array $data
     * @return Historial
     */
    public function registrar(array $data): Historial
    {
        return Historial::create([
            'idCiclo' => $data['idCiclo'] ?? null,
            'entidad' => $data['entidad'],
            'idEntidad' => $data['idEntidad'] ?? null,
            'accion' => $data['accion'],
            'descripcion' => $data['descripcion'],
            'datosAnteriores' => $data['datosAnteriores'] ?? null,
            'datosNuevos' => $data['datosNuevos'] ?? null,
            'idUsuario' => $data['idUsuario'] ?? auth()->id(),
            'fechaHora' => now(),
        ]);
    }

    /**
     * Obtiene estadísticas del historial por ciclo.
     *
     * @param int $idCiclo
     * @return array
     */
    public function getEstadisticasPorCiclo(int $idCiclo): array
    {
        $historial = Historial::where('idCiclo', $idCiclo)->get();

        return [
            'total' => $historial->count(),
            'porEntidad' => $historial->groupBy('entidad')->map->count(),
            'porAccion' => $historial->groupBy('accion')->map->count(),
            'ultimaActividad' => $historial->sortByDesc('fechaHora')->first()?->fechaHora,
        ];
    }

    /**
     * Obtiene las entidades únicas registradas en el historial.
     *
     * @return Collection
     */
    public function getEntidadesUnicas(): Collection
    {
        return Historial::select('entidad')
            ->distinct()
            ->orderBy('entidad')
            ->pluck('entidad');
    }

    /**
     * Obtiene las acciones únicas registradas en el historial.
     *
     * @return Collection
     */
    public function getAccionesUnicas(): Collection
    {
        return Historial::select('accion')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');
    }
}
