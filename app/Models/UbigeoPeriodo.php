<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UbigeoPeriodo extends Model
{
    protected $table = 'ODS.TAB_UBIGEO_PERIODO';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'idUbigeo',
        'idGeosegmento',
        'idPeriodoCiclo'
    ];

    // Relación con Ubigeo
    public function ubigeo()
    {
        return $this->belongsTo(Ubigeo::class, 'idUbigeo', 'idUbigeo');
    }

    // Relación con Geosegmento
    public function geosegmento()
    {
        return $this->belongsTo(Geosegmento::class, 'idGeosegmento', 'idGeosegmento');
    }

    // Relación con PeriodoCiclo
    public function periodoCiclo()
    {
        return $this->belongsTo(PeriodoCiclo::class, 'idPeriodoCiclo', 'idPeriodoCiclo');
    }
}
