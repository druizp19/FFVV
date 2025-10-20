<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FranqLinea extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_FRANQLINEA';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idFranqLinea';

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
        'idFranquicia',
        'idLinea',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idFranquicia' => 'integer',
        'idLinea' => 'integer',
    ];

    /**
     * Relación con el modelo Franquicia.
     * Una asignación pertenece a una franquicia.
     *
     * @return BelongsTo
     */
    public function franquicia(): BelongsTo
    {
        return $this->belongsTo(Franquicia::class, 'idFranquicia', 'idFranquicia');
    }

    /**
     * Relación con el modelo Linea.
     * Una asignación pertenece a una línea.
     *
     * @return BelongsTo
     */
    public function linea(): BelongsTo
    {
        return $this->belongsTo(Linea::class, 'idLinea', 'idLinea');
    }

    /**
     * Relación con el modelo Producto.
     * Una franquicia-línea puede tener muchos productos.
     *
     * @return HasMany
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'idFranqLinea', 'idFranqLinea');
    }
}

