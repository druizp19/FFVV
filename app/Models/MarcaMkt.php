<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarcaMkt extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_MARCAMKT';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idMarcaMkt';

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
        'idMercado',
        'idMarca',
        'marcaMkt',
        'idEstado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idMercado' => 'integer',
        'idMarca' => 'integer',
        'idEstado' => 'integer',
    ];

    /**
     * Relación con el modelo Mercado.
     * Una marca-mercado pertenece a un mercado.
     *
     * @return BelongsTo
     */
    public function mercado(): BelongsTo
    {
        return $this->belongsTo(Mercado::class, 'idMercado', 'idMercado');
    }

    /**
     * Relación con el modelo Marca.
     * Una marca-mercado pertenece a una marca.
     *
     * @return BelongsTo
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'idMarca', 'idMarca');
    }

    /**
     * Relación con el modelo Estado.
     * Una marca-mercado pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Scope para filtrar marcas-mercado activas.
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
     * Scope para filtrar por marca.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idMarca
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorMarca($query, int $idMarca)
    {
        return $query->where('idMarca', $idMarca);
    }

    /**
     * Scope para filtrar por mercado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idMercado
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorMercado($query, int $idMercado)
    {
        return $query->where('idMercado', $idMercado);
    }

    /**
     * Relación con el modelo Producto.
     * Una marca-mercado puede tener muchos productos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'idMarcaMkt', 'idMarcaMkt');
    }
}

