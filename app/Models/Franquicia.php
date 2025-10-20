<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Franquicia extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_FRANQUICIA';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idFranquicia';

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
        'franquicia',
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
     * Una franquicia pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo UneFranq.
     * Una franquicia puede tener muchas asignaciones une-franquicia.
     *
     * @return HasMany
     */
    public function unesFranquicias(): HasMany
    {
        return $this->hasMany(UneFranq::class, 'idFranquicia', 'idFranquicia');
    }

    /**
     * Relación con el modelo Marca.
     * Una franquicia puede tener muchas marcas.
     *
     * @return HasMany
     */
    public function marcas(): HasMany
    {
        return $this->hasMany(Marca::class, 'idFranquicia', 'idFranquicia');
    }

    /**
     * Relación con el modelo MarcFranUne.
     * Una franquicia puede tener muchas marcas-franquicia-une.
     *
     * @return HasMany
     */
    public function marcasFranquiciasUnes(): HasMany
    {
        return $this->hasMany(MarcFranUne::class, 'idFranquicia', 'idFranquicia');
    }

    /**
     * Relación con el modelo FranqLinea.
     * Una franquicia puede tener muchas asignaciones franquicia-línea.
     *
     * @return HasMany
     */
    public function franquiciasLineas(): HasMany
    {
        return $this->hasMany(FranqLinea::class, 'idFranquicia', 'idFranquicia');
    }

    /**
     * Scope para filtrar franquicias activas.
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

