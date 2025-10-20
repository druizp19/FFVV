<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_PRODUCTO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idProducto';

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
        'idCiclo',
        'idFranqLinea',
        'idMarcaMkt',
        'idCore',
        'idCuota',
        'idPromocion',
        'idAlcance',
        'idEstado',
        'fechaModificacion',
        'fechaCierre',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idCiclo' => 'integer',
        'idFranqLinea' => 'integer',
        'idMarcaMkt' => 'integer',
        'idCore' => 'integer',
        'idCuota' => 'integer',
        'idPromocion' => 'integer',
        'idAlcance' => 'integer',
        'idEstado' => 'integer',
        'fechaModificacion' => 'date',
        'fechaCierre' => 'date',
    ];

    /**
     * Relación con el modelo Ciclo.
     * Un producto pertenece a un ciclo.
     *
     * @return BelongsTo
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo', 'idCiclo');
    }

    /**
     * Relación con el modelo FranqLinea.
     * Un producto pertenece a una franquicia-línea.
     *
     * @return BelongsTo
     */
    public function franqLinea(): BelongsTo
    {
        return $this->belongsTo(FranqLinea::class, 'idFranqLinea', 'idFranqLinea');
    }

    /**
     * Relación con el modelo MarcaMkt.
     * Un producto pertenece a una marca-mercado.
     *
     * @return BelongsTo
     */
    public function marcaMkt(): BelongsTo
    {
        return $this->belongsTo(MarcaMkt::class, 'idMarcaMkt', 'idMarcaMkt');
    }

    /**
     * Relación con el modelo Core.
     * Un producto pertenece a un core.
     *
     * @return BelongsTo
     */
    public function core(): BelongsTo
    {
        return $this->belongsTo(Core::class, 'idCore', 'idCore');
    }

    /**
     * Relación con el modelo Cuota.
     * Un producto pertenece a una cuota.
     *
     * @return BelongsTo
     */
    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class, 'idCuota', 'idCuota');
    }

    /**
     * Relación con el modelo Promocion.
     * Un producto pertenece a una promoción.
     *
     * @return BelongsTo
     */
    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class, 'idPromocion', 'idPromocion');
    }

    /**
     * Relación con el modelo Alcance.
     * Un producto pertenece a un alcance.
     *
     * @return BelongsTo
     */
    public function alcance(): BelongsTo
    {
        return $this->belongsTo(Alcance::class, 'idAlcance', 'idAlcance');
    }

    /**
     * Relación con el modelo Estado.
     * Un producto pertenece a un estado.
     *
     * @return BelongsTo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Scope para filtrar productos activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->whereHas('estado', function ($q) {
            $q->where('estado', 'Activo');
        });
    }

    /**
     * Scope para filtrar productos por ciclo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idCiclo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorCiclo($query, int $idCiclo)
    {
        return $query->where('idCiclo', $idCiclo);
    }

    /**
     * Scope para filtrar productos por promoción.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idPromocion
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorPromocion($query, int $idPromocion)
    {
        return $query->where('idPromocion', $idPromocion);
    }

    /**
     * Scope para filtrar productos sin fecha de cierre.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSinCierre($query)
    {
        return $query->whereNull('fechaCierre');
    }

    /**
     * Relación con el modelo FuerzaVenta.
     * Un producto puede tener muchas fuerzas de venta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fuerzasVenta()
    {
        return $this->hasMany(FuerzaVenta::class, 'idProducto', 'idProducto');
    }

    /**
     * Genera una clave única para el producto basada en sus relaciones.
     * Usa separador "_" para evitar ambigüedades en la concatenación.
     *
     * @return string
     */
    public function generarClaveUnica(): string
    {
        return implode('_', [
            $this->idCiclo,
            $this->idFranqLinea,
            $this->idMarcaMkt,
            $this->idCore,
            $this->idCuota,
            $this->idPromocion,
            $this->idAlcance,
        ]);
    }

    /**
     * Genera una clave única para un conjunto de datos de producto.
     * Usa separador "_" para evitar ambigüedades en la concatenación.
     *
     * @param array $data
     * @return string
     */
    public static function generarClaveUnicaDesdeArray(array $data): string
    {
        return implode('_', [
            $data['idCiclo'] ?? null,
            $data['idFranqLinea'] ?? null,
            $data['idMarcaMkt'] ?? null,
            $data['idCore'] ?? null,
            $data['idCuota'] ?? null,
            $data['idPromocion'] ?? null,
            $data['idAlcance'] ?? null,
        ]);
    }

    /**
     * Verifica si ya existe un producto con la misma combinación de parámetros.
     *
     * @param array $data
     * @param int|null $excludeId ID del producto a excluir (para actualizaciones)
     * @return bool
     */
    public static function existeDuplicado(array $data, ?int $excludeId = null): bool
    {
        $query = static::where([
            'idCiclo' => $data['idCiclo'],
            'idFranqLinea' => $data['idFranqLinea'],
            'idMarcaMkt' => $data['idMarcaMkt'],
            'idCore' => $data['idCore'],
            'idCuota' => $data['idCuota'],
            'idPromocion' => $data['idPromocion'],
            'idAlcance' => $data['idAlcance'],
        ]);

        if ($excludeId) {
            $query->where('idProducto', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Boot del modelo para validaciones automáticas.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($producto) {
            if (static::existeDuplicado($producto->toArray())) {
                $claveUnica = $producto->generarClaveUnica();
                throw new \Exception("Ya existe un producto con la combinación: {$claveUnica}");
            }
        });

        static::updating(function ($producto) {
            if (static::existeDuplicado($producto->toArray(), $producto->idProducto)) {
                $claveUnica = $producto->generarClaveUnica();
                throw new \Exception("Ya existe otro producto con la combinación: {$claveUnica}");
            }
        });
    }
}

