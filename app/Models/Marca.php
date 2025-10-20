<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marca extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_MARCA';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idMarca';

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
        'marca',
        'idFranquicia',
        'idEstado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idFranquicia' => 'integer',
        'idEstado' => 'integer',
    ];

    /**
     * Relación con el modelo Franquicia.
     * Una marca pertenece a una franquicia.
     *
     * @return BelongsTo
     */
    public function franquicia(): BelongsTo
    {
        return $this->belongsTo(Franquicia::class, 'idFranquicia', 'idFranquicia');
    }

    /**
     * Relación con el modelo Estado.
     * Una marca pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo MarcaMkt.
     * Una marca puede tener muchos mercados asignados.
     *
     * @return HasMany
     */
    public function marcasMercados(): HasMany
    {
        return $this->hasMany(MarcaMkt::class, 'idMarca', 'idMarca');
    }

    /**
     * Relación con el modelo MarcFranUne.
     * Una marca puede tener muchas asignaciones franquicia-une.
     *
     * @return HasMany
     */
    public function marcasFranquiciasUnes(): HasMany
    {
        return $this->hasMany(MarcFranUne::class, 'idMarca', 'idMarca');
    }

    /**
     * Scope para filtrar marcas activas.
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
     * Scope para filtrar marcas por franquicia.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idFranquicia
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorFranquicia($query, int $idFranquicia)
    {
        return $query->where('idFranquicia', $idFranquicia);
    }
}

