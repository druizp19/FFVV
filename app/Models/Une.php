<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Une extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_UNE';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idUne';

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
        'unidadNegocio',
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
     * Una unidad de negocio pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Empleado.
     * Una unidad de negocio puede tener muchos empleados.
     *
     * @return HasMany
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'idUne', 'idUne');
    }

    /**
     * Relación con el modelo UneFranq.
     * Una unidad de negocio puede tener muchas asignaciones une-franquicia.
     *
     * @return HasMany
     */
    public function unesFranquicias(): HasMany
    {
        return $this->hasMany(UneFranq::class, 'idUne', 'idUne');
    }

    /**
     * Relación con el modelo MarcFranUne.
     * Una unidad de negocio puede tener muchas marcas-franquicia-une.
     *
     * @return HasMany
     */
    public function marcasFranquiciasUnes(): HasMany
    {
        return $this->hasMany(MarcFranUne::class, 'idUne', 'idUne');
    }

    /**
     * Scope para filtrar unidades de negocio activas.
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

