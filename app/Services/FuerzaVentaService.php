<?php

namespace App\Services;

use App\Repositories\FuerzaVentaRepository;
use App\Models\FuerzaVenta;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class FuerzaVentaService
{
    protected FuerzaVentaRepository $fuerzaVentaRepository;

    public function __construct(FuerzaVentaRepository $fuerzaVentaRepository)
    {
        $this->fuerzaVentaRepository = $fuerzaVentaRepository;
    }

    public function getAllFuerzasVenta(): Collection
    {
        return $this->fuerzaVentaRepository->getAll();
    }

    public function getFuerzaVentaById(int $id): ?FuerzaVenta
    {
        return $this->fuerzaVentaRepository->findById($id);
    }

    public function crearFuerzaVenta(array $data): array
    {
        // Validar fechas si están presentes
        if (isset($data['fechaModificacion']) && isset($data['fechaCierre'])) {
            $fechaModificacion = Carbon::parse($data['fechaModificacion']);
            $fechaCierre = Carbon::parse($data['fechaCierre']);

            if ($fechaCierre->lessThan($fechaModificacion)) {
                return [
                    'success' => false,
                    'message' => 'La fecha de cierre debe ser posterior a la fecha de modificación.'
                ];
            }
        }

        try {
            $fuerzaVenta = $this->fuerzaVentaRepository->create($data);

            return [
                'success' => true,
                'message' => 'Fuerza de venta creada exitosamente.',
                'data' => $fuerzaVenta
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la fuerza de venta: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarFuerzaVenta(int $id, array $data): array
    {
        // Validar fechas si están presentes
        if (isset($data['fechaModificacion']) && isset($data['fechaCierre'])) {
            $fechaModificacion = Carbon::parse($data['fechaModificacion']);
            $fechaCierre = Carbon::parse($data['fechaCierre']);

            if ($fechaCierre->lessThan($fechaModificacion)) {
                return [
                    'success' => false,
                    'message' => 'La fecha de cierre debe ser posterior a la fecha de modificación.'
                ];
            }
        }

        try {
            $updated = $this->fuerzaVentaRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Fuerza de venta no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Fuerza de venta actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la fuerza de venta: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarFuerzaVenta(int $id): array
    {
        try {
            $deleted = $this->fuerzaVentaRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Fuerza de venta no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Fuerza de venta eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la fuerza de venta: ' . $e->getMessage()
            ];
        }
    }

    public function cerrarFuerzaVenta(int $id): array
    {
        try {
            $updated = $this->fuerzaVentaRepository->update($id, [
                'fechaCierre' => Carbon::now()
            ]);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Fuerza de venta no encontrada.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Fuerza de venta cerrada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cerrar la fuerza de venta: ' . $e->getMessage()
            ];
        }
    }

    public function obtenerPorCiclo(int $idCiclo): Collection
    {
        return $this->fuerzaVentaRepository->getPorCiclo($idCiclo);
    }

    public function obtenerPorEmpleado(int $idEmpleado): Collection
    {
        return $this->fuerzaVentaRepository->getPorEmpleado($idEmpleado);
    }

    public function obtenerPorPeriodoComision(string $periodo): Collection
    {
        return $this->fuerzaVentaRepository->getPorPeriodoComision($periodo);
    }
}

