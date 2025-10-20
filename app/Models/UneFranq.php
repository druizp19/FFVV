<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UneFranq extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_UNEFRANQ';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idUneFranq';

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
        'idUne',
        'idFranquicia',
        'idEmpleado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idUne' => 'integer',
        'idFranquicia' => 'integer',
        'idEmpleado' => 'integer',
    ];

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
     * Relación con el modelo Empleado.
     * Una asignación pertenece a un empleado.
     *
     * @return BelongsTo
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'idEmpleado', 'idEmpleado');
    }

    /**
     * Relación con el modelo MarcFranUne.
     * Una asignación puede tener muchas marcas asignadas.
     *
     * @return HasMany
     */
    public function marcasFranquiciasUnes(): HasMany
    {
        return $this->hasMany(MarcFranUne::class, 'idUneFranq', 'idUneFranq');
    }
}

