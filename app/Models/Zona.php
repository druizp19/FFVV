<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_ZONA';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idZona';

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
        'zona',
        'idEstado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idEstado' => 'integer',
    ];

    /**
     * Relación con el modelo Estado.
     * Una zona pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo ZonaEmp.
     * Una zona puede tener muchos empleados asignados.
     *
     * @return HasMany
     */
    public function zonasEmpleados(): HasMany
    {
        return $this->hasMany(ZonaEmp::class, 'idZona', 'idZona');
    }

    /**
     * Relación con el modelo ZonaGeo.
     * Una zona puede tener muchos geosegmentos asignados.
     *
     * @return HasMany
     */
    public function zonasGeosegmentos(): HasMany
    {
        return $this->hasMany(ZonaGeo::class, 'idZona', 'idZona');
    }

    /**
     * Scope para filtrar zonas activas.
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
}

