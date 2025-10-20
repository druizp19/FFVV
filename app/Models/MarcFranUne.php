<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarcFranUne extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_MARCFRANUNE';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idMarcaFranq_UNE';

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
        'idMarca',
        'idFranquicia',
        'idUne',
        'idUneFranq',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idMarca' => 'integer',
        'idFranquicia' => 'integer',
        'idUne' => 'integer',
        'idUneFranq' => 'integer',
    ];

    /**
     * Relación con el modelo Marca.
     * Una asignación pertenece a una marca.
     *
     * @return BelongsTo
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'idMarca', 'idMarca');
    }

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
     * Relación con el modelo Une.
     * Una asignación pertenece a una unidad de negocio.
     *
     * @return BelongsTo
     */
    public function une(): BelongsTo
    {
        return $this->belongsTo(Une::class, 'idUne', 'idUne');
    }

    /**
     * Relación con el modelo UneFranq.
     * Una asignación pertenece a una uneFranq.
     *
     * @return BelongsTo
     */
    public function uneFranq(): BelongsTo
    {
        return $this->belongsTo(UneFranq::class, 'idUneFranq', 'idUneFranq');
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
     * Scope para filtrar por franquicia.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idFranquicia
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorFranquicia($query, int $idFranquicia)
    {
        return $query->where('idFranquicia', $idFranquicia);
    }

    /**
     * Scope para filtrar por UNE.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idUne
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorUne($query, int $idUne)
    {
        return $query->where('idUne', $idUne);
    }
}

