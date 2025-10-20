<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuerzaVenta extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_FUERZAVENTA';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idFuerza';

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
        'idZonaEmp',
        'idProducto',
        'idEmpleado',
        'fechaModificacion',
        'fechaCierre',
        'idEstado',
        'periodoComision',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idCiclo' => 'integer',
        'idZonaEmp' => 'integer',
        'idProducto' => 'integer',
        'idEmpleado' => 'integer',
        'idEstado' => 'integer',
        'fechaModificacion' => 'date',
        'fechaCierre' => 'date',
    ];

    /**
     * Relación con el modelo Ciclo.
     * Una fuerza de venta pertenece a un ciclo.
     *
     * @return BelongsTo
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Relación con el modelo ZonaEmp.
     * Una fuerza de venta pertenece a una asignación zona-empleado.
     *
     * @return BelongsTo
     */
    public function zonaEmpleado(): BelongsTo
    {
        return $this->belongsTo(ZonaEmp::class, 'idZonaEmp', 'idZonaEmp');
    }

    /**
     * Relación con el modelo Producto.
     * Una fuerza de venta pertenece a un producto.
     *
     * @return BelongsTo
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }

    /**
     * Relación con el modelo Empleado.
     * Una fuerza de venta pertenece a un empleado.
     *
     * @return BelongsTo
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'idEmpleado', 'idEmpleado');
    }

    /**
     * Relación con el modelo Estado.
     * Una fuerza de venta pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Scope para filtrar fuerzas de venta activas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivas($query)
    {
        return $query->whereHas('estado', function ($q) {
            $q->where('estado', 'Activo');
        });
    }

    /**
     * Scope para filtrar fuerzas de venta por ciclo.
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
     * Scope para filtrar fuerzas de venta por empleado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idEmpleado
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorEmpleado($query, int $idEmpleado)
    {
        return $query->where('idEmpleado', $idEmpleado);
    }

    /**
     * Scope para filtrar fuerzas de venta por producto.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idProducto
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorProducto($query, int $idProducto)
    {
        return $query->where('idProducto', $idProducto);
    }

    /**
     * Scope para filtrar fuerzas de venta sin fecha de cierre.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSinCierre($query)
    {
        return $query->whereNull('fechaCierre');
    }

    /**
     * Scope para filtrar fuerzas de venta por período de comisión.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $periodo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorPeriodoComision($query, string $periodo)
    {
        return $query->where('periodoComision', $periodo);
    }

    /**
     * Verifica si la fuerza de venta está cerrada.
     *
     * @return bool
     */
    public function estaCerrada(): bool
    {
        return $this->fechaCierre !== null;
    }

    /**
     * Verifica si la fuerza de venta está abierta.
     *
     * @return bool
     */
    public function estaAbierta(): bool
    {
        return $this->fechaCierre === null;
    }
}

