<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_EMPLEADO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idEmpleado';

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
        'idCargo',
        'idArea',
        'idUne',
        'idEstado',
        'dni',
        'nombre',
        'apeNombre',
        'correo',
        'celular',
        'fechaIngreso',
        'fechaCese',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idCargo' => 'integer',
        'idArea' => 'integer',
        'idUne' => 'integer',
        'idEstado' => 'integer',
        'fechaIngreso' => 'date',
        'fechaCese' => 'date',
    ];

    /**
     * Relación con el modelo Cargo.
     * Un empleado pertenece a un cargo.
     *
     * @return BelongsTo
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'idCargo', 'idCargo');
    }

    /**
     * Relación con el modelo Area.
     * Un empleado pertenece a un área.
     *
     * @return BelongsTo
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'idArea', 'idArea');
    }

    /**
     * Relación con el modelo Une.
     * Un empleado pertenece a una unidad de negocio.
     *
     * @return BelongsTo
     */
    public function une(): BelongsTo
    {
        return $this->belongsTo(Une::class, 'idUne', 'idUne');
    }

    /**
     * Relación con el modelo Estado.
     * Un empleado pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo ZonaEmp.
     * Un empleado puede estar asignado a muchas zonas.
     *
     * @return HasMany
     */
    public function zonasAsignadas(): HasMany
    {
        return $this->hasMany(ZonaEmp::class, 'idEmpleado', 'idEmpleado');
    }

    /**
     * Relación con el modelo UneFranq.
     * Un empleado puede tener muchas asignaciones une-franquicia.
     *
     * @return HasMany
     */
    public function unesFranquicias(): HasMany
    {
        return $this->hasMany(UneFranq::class, 'idEmpleado', 'idEmpleado');
    }

    /**
     * Obtiene el nombre completo del empleado.
     *
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apeNombre);
    }

    /**
     * Scope para filtrar empleados activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->whereHas('estado', function ($q) {
            $q->where('estado', 'Activo');
        });
    }

    /**
     * Scope para filtrar empleados que no tienen fecha de cese.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSinCese($query)
    {
        return $query->whereNull('fechaCese');
    }

    /**
     * Scope para filtrar empleados por cargo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idCargo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorCargo($query, int $idCargo)
    {
        return $query->where('idCargo', $idCargo);
    }

    /**
     * Relación con el modelo FuerzaVenta.
     * Un empleado puede tener muchas fuerzas de venta.
     *
     * @return HasMany
     */
    public function fuerzasVenta(): HasMany
    {
        return $this->hasMany(FuerzaVenta::class, 'idEmpleado', 'idEmpleado');
    }
}

