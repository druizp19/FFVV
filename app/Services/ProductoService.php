<?php

namespace App\Services;

use App\Repositories\ProductoRepository;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class ProductoService
{
    protected ProductoRepository $productoRepository;

    public function __construct(ProductoRepository $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    public function getAllProductos(): Collection
    {
        return $this->productoRepository->getAll();
    }

    public function getProductoById(int $id): ?Producto
    {
        return $this->productoRepository->findById($id);
    }

    public function crearProducto(array $data): array
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

        // Validar duplicados antes de crear
        if ($this->productoRepository->existeDuplicado($data)) {
            $claveUnica = $this->productoRepository->generarClaveUnica($data);
            return [
                'success' => false,
                'message' => "Ya existe un producto con la combinación: {$claveUnica}. No se pueden crear productos duplicados."
            ];
        }

        try {
            $producto = $this->productoRepository->create($data);

            return [
                'success' => true,
                'message' => 'Producto creado exitosamente.',
                'data' => $producto
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el producto: ' . $e->getMessage()
            ];
        }
    }

    public function actualizarProducto(int $id, array $data): array
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

        // Validar duplicados antes de actualizar (excluyendo el producto actual)
        if ($this->productoRepository->existeDuplicado($data, $id)) {
            $claveUnica = $this->productoRepository->generarClaveUnica($data);
            return [
                'success' => false,
                'message' => "Ya existe otro producto con la combinación: {$claveUnica}. No se pueden crear productos duplicados."
            ];
        }

        try {
            $updated = $this->productoRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Producto actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarProducto(int $id): array
    {
        try {
            $deleted = $this->productoRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Producto eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ];
        }
    }

    public function cerrarProducto(int $id): array
    {
        try {
            $updated = $this->productoRepository->update($id, [
                'fechaCierre' => Carbon::now()
            ]);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Producto cerrado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cerrar el producto: ' . $e->getMessage()
            ];
        }
    }
}

