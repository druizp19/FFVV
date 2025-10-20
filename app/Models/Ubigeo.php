<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ubigeo extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_UBIGEO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idUbigeo';

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
        'idGeosegmento',
        'idBrick',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'region',
        'superficie',
        'longitud',
        'latitud',
        'idEstado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idGeosegmento' => 'integer',
        'idBrick' => 'integer',
        'idEstado' => 'integer',
    ];

    /**
     * Relación con el modelo Geosegmento.
     * Un ubigeo pertenece a un geosegmento.
     *
     * @return BelongsTo
     */
    public function geosegmento(): BelongsTo
    {
        return $this->belongsTo(Geosegmento::class, 'idGeosegmento', 'idGeosegmento');
    }

    /**
     * Relación con el modelo Estado.
     * Un ubigeo pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Scope para filtrar ubigeos activos.
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
     * Scope para filtrar ubigeos por departamento.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $departamento
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorDepartamento($query, string $departamento)
    {
        return $query->where('departamento', 'like', "%{$departamento}%");
    }

    /**
     * Scope para filtrar ubigeos por provincia.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $provincia
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorProvincia($query, string $provincia)
    {
        return $query->where('provincia', 'like', "%{$provincia}%");
    }

    /**
     * Scope para filtrar ubigeos por distrito.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $distrito
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorDistrito($query, string $distrito)
    {
        return $query->where('distrito', 'like', "%{$distrito}%");
    }

    /**
     * Scope para filtrar ubigeos por región.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $region
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorRegion($query, string $region)
    {
        return $query->where('region', 'like', "%{$region}%");
    }

    /**
     * Scope para filtrar ubigeos por geosegmento.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idGeosegmento
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorGeosegmento($query, int $idGeosegmento)
    {
        return $query->where('idGeosegmento', $idGeosegmento);
    }
}

