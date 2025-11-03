<?php

namespace App\Traits;

use App\Models\Historial;

trait RegistraHistorial
{
    /**
     * Boot del trait para registrar eventos automáticamente.
     */
    protected static function bootRegistraHistorial()
    {
        // Registrar creación
        static::created(function ($model) {
            $model->registrarEnHistorial('Crear', "Se creó {$model->getNombreEntidad()}: {$model->getDescripcionHistorial()}", [
                'datosNuevos' => $model->getDatosParaHistorial(),
            ]);
        });

        // Registrar actualización
        static::updated(function ($model) {
            $cambios = $model->getCambiosRelevantes();
            
            if (!empty($cambios)) {
                $model->registrarEnHistorial('Actualizar', "Se actualizó {$model->getNombreEntidad()}: {$model->getDescripcionHistorial()}", [
                    'datosAnteriores' => $cambios['anterior'],
                    'datosNuevos' => $cambios['nuevo'],
                ]);
            }
        });

        // Registrar eliminación (soft delete o cambio de estado)
        static::deleting(function ($model) {
            $model->registrarEnHistorial('Eliminar', "Se eliminó {$model->getNombreEntidad()}: {$model->getDescripcionHistorial()}", [
                'datosAnteriores' => $model->getDatosParaHistorial(),
            ]);
        });
    }

    /**
     * Registra un evento en el historial.
     *
     * @param string $accion
     * @param string $descripcion
     * @param array $opciones
     * @return void
     */
    public function registrarEnHistorial(string $accion, string $descripcion, array $opciones = []): void
    {
        try {
            Historial::create([
                'idCiclo' => $this->getIdCicloParaHistorial(),
                'entidad' => $this->getNombreEntidad(),
                'idEntidad' => $this->getKey(),
                'accion' => $accion,
                'descripcion' => $descripcion,
                'datosAnteriores' => $opciones['datosAnteriores'] ?? null,
                'datosNuevos' => $opciones['datosNuevos'] ?? null,
                'idUsuario' => auth()->id(),
                'fechaHora' => now(),
            ]);
        } catch (\Exception $e) {
            // Log error pero no interrumpir la operación principal
            \Log::error('Error al registrar en historial: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el nombre de la entidad para el historial.
     *
     * @return string
     */
    protected function getNombreEntidad(): string
    {
        return class_basename(get_class($this));
    }

    /**
     * Obtiene una descripción legible del modelo para el historial.
     *
     * @return string
     */
    protected function getDescripcionHistorial(): string
    {
        // Intentar obtener un campo descriptivo común
        $campos = ['nombre', 'zona', 'ciclo', 'descripcion', 'titulo'];
        
        foreach ($campos as $campo) {
            if (isset($this->$campo)) {
                return $this->$campo;
            }
        }
        
        return "ID: {$this->getKey()}";
    }

    /**
     * Obtiene los datos relevantes del modelo para el historial.
     *
     * @return array
     */
    protected function getDatosParaHistorial(): array
    {
        // Excluir campos sensibles o innecesarios
        $excluir = ['password', 'remember_token', 'created_at', 'updated_at'];
        
        return collect($this->getAttributes())
            ->except($excluir)
            ->toArray();
    }

    /**
     * Obtiene los cambios relevantes entre el estado anterior y nuevo.
     *
     * @return array
     */
    protected function getCambiosRelevantes(): array
    {
        $cambios = $this->getDirty();
        
        if (empty($cambios)) {
            return [];
        }
        
        $anterior = [];
        $nuevo = [];
        
        foreach ($cambios as $campo => $valorNuevo) {
            $anterior[$campo] = $this->getOriginal($campo);
            $nuevo[$campo] = $valorNuevo;
        }
        
        return [
            'anterior' => $anterior,
            'nuevo' => $nuevo,
        ];
    }

    /**
     * Obtiene el ID del ciclo relacionado para el historial.
     *
     * @return int|null
     */
    protected function getIdCicloParaHistorial(): ?int
    {
        // Si el modelo tiene un campo idCiclo directamente
        if (isset($this->idCiclo)) {
            return $this->idCiclo;
        }
        
        // Si el modelo tiene una relación ciclo
        if (method_exists($this, 'ciclo') && $this->ciclo) {
            return $this->ciclo->idCiclo;
        }
        
        return null;
    }
}
