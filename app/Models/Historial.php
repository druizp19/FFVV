<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Historial extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_HISTORIAL';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idHistorial';

    /**
     * Indica si el modelo debe usar timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'idCiclo',
        'entidad',
        'idEntidad',
        'accion',
        'descripcion',
        'datosAnteriores',
        'datosNuevos',
        'idUsuario',
        'fechaHora',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idCiclo' => 'integer',
        'idEntidad' => 'integer',
        'datosAnteriores' => 'array',
        'datosNuevos' => 'array',
        'idUsuario' => 'integer',
        'fechaHora' => 'datetime',
    ];

    /**
     * Relación con el modelo Ciclo.
     *
     * @return BelongsTo
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Relación con el modelo User.
     *
     * @return BelongsTo
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUsuario', 'id');
    }

    /**
     * Scope para filtrar por ciclo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idCiclo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorCiclo($query, int $idCiclo)
    {
        return $query->where('idCiclo', $idCiclo);
    }

    /**
     * Scope para filtrar por entidad.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entidad
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorEntidad($query, string $entidad)
    {
        return $query->where('entidad', $entidad);
    }

    /**
     * Scope para filtrar por acción.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $accion
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Scope para filtrar por rango de fechas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $desde
     * @param string $hasta
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorRangoFechas($query, string $desde, string $hasta)
    {
        return $query->whereBetween('fechaHora', [$desde, $hasta]);
    }

    /**
     * Obtiene el icono según la acción.
     *
     * @return string
     */
    public function getIconoAccion(): string
    {
        return match($this->accion) {
            'Crear' => 'plus-circle',
            'Actualizar' => 'edit',
            'Eliminar' => 'trash',
            'Asignar' => 'link',
            'Desasignar' => 'unlink',
            'Activar' => 'check-circle',
            'Desactivar' => 'x-circle',
            default => 'activity',
        };
    }

    /**
     * Obtiene el color según la acción.
     *
     * @return string
     */
    public function getColorAccion(): string
    {
        return match($this->accion) {
            'Crear' => 'success',
            'Actualizar' => 'info',
            'Eliminar' => 'danger',
            'Asignar' => 'primary',
            'Desasignar' => 'warning',
            'Activar' => 'success',
            'Desactivar' => 'secondary',
            default => 'secondary',
        };
    }
}
