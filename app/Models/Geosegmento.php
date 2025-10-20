<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Geosegmento extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_GEOSEGMENTO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idGeosegmento';

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
        'geosegmento',
        'lugar',
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
     * Un geosegmento pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo ZonaGeo.
     * Un geosegmento puede estar asignado a muchas zonas.
     *
     * @return HasMany
     */
    public function zonasAsignadas(): HasMany
    {
        return $this->hasMany(ZonaGeo::class, 'idGeosegmento', 'idGeosegmento');
    }

    /**
     * Relación con el modelo Ubigeo.
     * Un geosegmento puede tener muchos ubigeos.
     *
     * @return HasMany
     */
    public function ubigeos(): HasMany
    {
        return $this->hasMany(Ubigeo::class, 'idGeosegmento', 'idGeosegmento');
    }

    /**
     * Scope para filtrar geosegmentos activos.
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
     * Scope para filtrar geosegmentos por lugar.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $lugar
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorLugar($query, string $lugar)
    {
        return $query->where('lugar', 'like', "%{$lugar}%");
    }
}

