<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZonaGeo extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_ZONAGEO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idZonaGeo';

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
        'idZona',
        'idGeosegmento',
        'idEstado',
        'idCiclo',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idZona' => 'integer',
        'idGeosegmento' => 'integer',
        'idEstado' => 'integer',
        'idCiclo' => 'integer',
    ];

    /**
     * Relación con el modelo Zona.
     * Una asignación pertenece a una zona.
     *
     * @return BelongsTo
     */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'idZona', 'idZona');
    }

    /**
     * Relación con el modelo Geosegmento.
     * Una asignación pertenece a un geosegmento.
     *
     * @return BelongsTo
     */
    public function geosegmento(): BelongsTo
    {
        return $this->belongsTo(Geosegmento::class, 'idGeosegmento', 'idGeosegmento');
    }

    /**
     * Relación con el modelo Estado.
     * Una asignación pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Ciclo.
     * Una asignación pertenece a un ciclo.
     *
     * @return BelongsTo
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Scope para filtrar asignaciones activas.
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
     * Scope para filtrar asignaciones por ciclo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idCiclo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorCiclo($query, int $idCiclo)
    {
        return $query->where('idCiclo', $idCiclo);
    }
}

