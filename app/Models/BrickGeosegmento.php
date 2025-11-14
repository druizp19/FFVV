<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrickGeosegmento extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_BRICK_GEOSEGMENTO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idBrickGeosegmento';

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
        'idbrick',
        'idcanal',
        'idgeosegmento',
        'idperiodociclo',
        'fechaproceso',
        'idestado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idbrick' => 'integer',
        'idcanal' => 'integer',
        'idgeosegmento' => 'integer',
        'idperiodociclo' => 'integer',
        'fechaproceso' => 'date',
        'idestado' => 'integer',
    ];

    /**
     * Relación con el modelo Geosegmento.
     * Un brick-geosegmento pertenece a un geosegmento.
     *
     * @return BelongsTo
     */
    public function geosegmento(): BelongsTo
    {
        return $this->belongsTo(Geosegmento::class, 'idGeosegmento', 'idGeosegmento');
    }

    /**
     * Relación con el modelo PeriodoCiclo.
     * Un brick-geosegmento pertenece a un periodo-ciclo.
     *
     * @return BelongsTo
     */
    public function periodoCiclo(): BelongsTo
    {
        return $this->belongsTo(PeriodoCiclo::class, 'idPeriodoCiclo', 'idPeriodoCiclo');
    }

    /**
     * Relación con el modelo Estado.
     * Un brick-geosegmento pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }
}
