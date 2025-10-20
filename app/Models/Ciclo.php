<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Ciclo extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_CICLO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idCiclo';

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
        'ciclo',
        'fechaInicio',
        'fechaFin',
        'diasHabiles',
        'idEstado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fechaInicio' => 'date',
        'fechaFin' => 'date',
        'diasHabiles' => 'integer',
        'idEstado' => 'integer',
    ];

    /**
     * Calcula los días hábiles entre FechaInicio y FechaFin.
     * 
     * @return int
     */
    public function calcularDiasHabiles(): int
    {
        if (!$this->fechaInicio || !$this->fechaFin) {
            return 0;
        }

        $fechaInicio = Carbon::parse($this->fechaInicio);
        $fechaFin = Carbon::parse($this->fechaFin);

        // Calculamos la diferencia en días
        $dias = $fechaInicio->diffInDays($fechaFin);

        return (int) $dias;
    }

    /**
     * Obtiene el código del ciclo formateado.
     * 
     * @return string
     */
    public function getCodigoAttribute(): string
    {
        $year = Carbon::parse($this->fechaInicio)->format('Y');
        $month = Carbon::parse($this->fechaInicio)->format('m');
        
        return $year . $month;
    }

    /**
     * Obtiene el estado del ciclo (Abierto/Cerrado).
     * 
     * @return string
     */
    public function getEstadoAttribute(): string
    {
        $fechaActual = Carbon::now();
        $fechaInicio = Carbon::parse($this->fechaInicio);
        $fechaFin = Carbon::parse($this->fechaFin);

        if ($fechaActual->between($fechaInicio, $fechaFin)) {
            return 'Abierto';
        }

        return 'Cerrado';
    }

    /**
     * Scope para filtrar ciclos abiertos.
     */
    public function scopeAbiertos($query)
    {
        $fechaActual = Carbon::now();
        
        return $query->whereDate('fechaInicio', '<=', $fechaActual)
                     ->whereDate('fechaFin', '>=', $fechaActual);
    }

    /**
     * Scope para filtrar ciclos cerrados.
     */
    public function scopeCerrados($query)
    {
        $fechaActual = Carbon::now();
        
        return $query->where(function ($query) use ($fechaActual) {
            $query->whereDate('fechaFin', '<', $fechaActual)
                  ->orWhereDate('fechaInicio', '>', $fechaActual);
        });
    }

    /**
     * Relación con el modelo ZonaEmp.
     * Un ciclo puede tener muchas asignaciones zona-empleado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zonasEmpleados()
    {
        return $this->hasMany(ZonaEmp::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Relación con el modelo ZonaGeo.
     * Un ciclo puede tener muchas asignaciones zona-geosegmento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zonasGeosegmentos()
    {
        return $this->hasMany(ZonaGeo::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Relación con el modelo Producto.
     * Un ciclo puede tener muchos productos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Relación con el modelo FuerzaVenta.
     * Un ciclo puede tener muchas fuerzas de venta.
     *
     * @return HasMany
     */
    public function fuerzasVenta(): HasMany
    {
        return $this->hasMany(FuerzaVenta::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Relación con el modelo Estado.
     * Un ciclo pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }
}

