<?php

namespace App\Repositories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

class ProductoRepository
{
    /**
     * Obtiene todos los productos.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Producto::with([
            'ciclo',
            'franqLinea.franquicia',
            'franqLinea.linea',
            'marcaMkt.marca',
            'marcaMkt.mercado',
            'core',
            'cuota',
            'promocion',
            'alcance',
            'estado'
        ])->get();
    }

    /**
     * Obtiene un producto por su ID.
     *
     * @param int $id
     * @return Producto|null
     */
    public function findById(int $id): ?Producto
    {
        return Producto::with([
            'ciclo',
            'franqLinea.franquicia',
            'franqLinea.linea',
            'marcaMkt.marca',
            'marcaMkt.mercado',
            'core',
            'cuota',
            'promocion',
            'alcance',
            'estado'
        ])->find($id);
    }

    /**
     * Crea un nuevo producto.
     *
     * @param array $data
     * @return Producto
     */
    public function create(array $data): Producto
    {
        return Producto::create($data);
    }

    /**
     * Actualiza un producto existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $producto = $this->findById($id);
        
        if (!$producto) {
            return false;
        }

        return $producto->update($data);
    }

    /**
     * Elimina un producto.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $producto = $this->findById($id);
        
        if (!$producto) {
            return false;
        }

        return $producto->delete();
    }

    /**
     * Obtiene los productos activos.
     *
     * @return Collection
     */
    public function getActivos(): Collection
    {
        return Producto::activos()->with([
            'ciclo',
            'franqLinea.franquicia',
            'franqLinea.linea',
            'marcaMkt.marca',
            'marcaMkt.mercado',
            'core',
            'cuota',
            'promocion',
            'alcance',
            'estado'
        ])->get();
    }

    /**
     * Obtiene productos por ciclo.
     *
     * @param int $idCiclo
     * @return Collection
     */
    public function getPorCiclo(int $idCiclo): Collection
    {
        return Producto::porCiclo($idCiclo)->with([
            'ciclo',
            'franqLinea.franquicia',
            'franqLinea.linea',
            'marcaMkt.marca',
            'marcaMkt.mercado',
            'core',
            'cuota',
            'promocion',
            'alcance',
            'estado'
        ])->get();
    }

    /**
     * Obtiene productos por promoción.
     *
     * @param int $idPromocion
     * @return Collection
     */
    public function getPorPromocion(int $idPromocion): Collection
    {
        return Producto::porPromocion($idPromocion)->with([
            'ciclo',
            'franqLinea.franquicia',
            'franqLinea.linea',
            'marcaMkt.marca',
            'marcaMkt.mercado',
            'core',
            'cuota',
            'promocion',
            'alcance',
            'estado'
        ])->get();
    }

    /**
     * Obtiene productos sin fecha de cierre.
     *
     * @return Collection
     */
    public function getSinCierre(): Collection
    {
        return Producto::sinCierre()->with([
            'ciclo',
            'franqLinea.franquicia',
            'franqLinea.linea',
            'marcaMkt.marca',
            'marcaMkt.mercado',
            'core',
            'cuota',
            'promocion',
            'alcance',
            'estado'
        ])->get();
    }
}

