<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table = 'ODS.TAB_PERIODO';
    protected $primaryKey = 'idPeriodo';
    public $timestamps = false;

    protected $fillable = [
        'anio',
        'mes',
        'idEstado'
    ];

    // Relación con Estado
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    // Relación con PeriodoCiclo
    public function periodoCiclos()
    {
        return $this->hasMany(PeriodoCiclo::class, 'idPeriodo', 'idPeriodo');
    }
}
