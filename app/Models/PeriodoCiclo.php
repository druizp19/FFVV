<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoCiclo extends Model
{
    protected $table = 'ODS.TAB_PERIODO_CICLO';
    protected $primaryKey = 'idPeriodoCiclo';
    public $timestamps = false;

    protected $fillable = [
        'idPeriodo',
        'idCiclo',
        'idEstado'
    ];

    // Relación con Periodo
    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'idPeriodo', 'idPeriodo');
    }

    // Relación con Ciclo
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo', 'idCiclo');
    }

    // Relación con Estado
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    // Relación con UbigeoPeriodo
    public function ubigeoPeriodos()
    {
        return $this->hasMany(UbigeoPeriodo::class, 'idPeriodoCiclo', 'idPeriodoCiclo');
    }
}
