<?php

namespace App\Repositories;

use App\Models\FuerzaVenta;
use Illuminate\Database\Eloquent\Collection;

class FuerzaVentaRepository
{
    /**
     * Obtiene todas las fuerzas de venta.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return FuerzaVenta::with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->get();
    }

    /**
     * Obtiene una fuerza de venta por su ID.
     *
     * @param int $id
     * @return FuerzaVenta|null
     */
    public function findById(int $id): ?FuerzaVenta
    {
        return FuerzaVenta::with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->find($id);
    }

    /**
     * Crea una nueva fuerza de venta.
     *
     * @param array $data
     * @return FuerzaVenta
     */
    public function create(array $data): FuerzaVenta
    {
        return FuerzaVenta::create($data);
    }

    /**
     * Actualiza una fuerza de venta existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fuerzaVenta = $this->findById($id);
        
        if (!$fuerzaVenta) {
            return false;
        }

        return $fuerzaVenta->update($data);
    }

    /**
     * Elimina una fuerza de venta.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $fuerzaVenta = $this->findById($id);
        
        if (!$fuerzaVenta) {
            return false;
        }

        return $fuerzaVenta->delete();
    }

    /**
     * Obtiene las fuerzas de venta activas.
     *
     * @return Collection
     */
    public function getActivas(): Collection
    {
        return FuerzaVenta::activas()->with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->get();
    }

    /**
     * Obtiene fuerzas de venta por ciclo.
     *
     * @param int $idCiclo
     * @return Collection
     */
    public function getPorCiclo(int $idCiclo): Collection
    {
        return FuerzaVenta::porCiclo($idCiclo)->with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->get();
    }

    /**
     * Obtiene fuerzas de venta por empleado.
     *
     * @param int $idEmpleado
     * @return Collection
     */
    public function getPorEmpleado(int $idEmpleado): Collection
    {
        return FuerzaVenta::porEmpleado($idEmpleado)->with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->get();
    }

    /**
     * Obtiene fuerzas de venta por producto.
     *
     * @param int $idProducto
     * @return Collection
     */
    public function getPorProducto(int $idProducto): Collection
    {
        return FuerzaVenta::porProducto($idProducto)->with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->get();
    }

    /**
     * Obtiene fuerzas de venta sin cierre.
     *
     * @return Collection
     */
    public function getSinCierre(): Collection
    {
        return FuerzaVenta::sinCierre()->with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->get();
    }

    /**
     * Obtiene fuerzas de venta por período de comisión.
     *
     * @param string $periodo
     * @return Collection
     */
    public function getPorPeriodoComision(string $periodo): Collection
    {
        return FuerzaVenta::porPeriodoComision($periodo)->with([
            'ciclo',
            'zonaEmpleado.zona',
            'zonaEmpleado.empleado',
            'producto',
            'empleado',
            'estado'
        ])->get();
    }
}

