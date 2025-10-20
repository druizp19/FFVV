<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_ESTADO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idEstado';

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
        'estado',
    ];

    /**
     * Relación con el modelo Area.
     * Un estado puede tener muchas áreas.
     *
     * @return HasMany
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Alcance.
     * Un estado puede tener muchos alcances.
     *
     * @return HasMany
     */
    public function alcances(): HasMany
    {
        return $this->hasMany(Alcance::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Cargo.
     * Un estado puede tener muchos cargos.
     *
     * @return HasMany
     */
    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Core.
     * Un estado puede tener muchos cores.
     *
     * @return HasMany
     */
    public function cores(): HasMany
    {
        return $this->hasMany(Core::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Cuota.
     * Un estado puede tener muchas cuotas.
     *
     * @return HasMany
     */
    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Une.
     * Un estado puede tener muchas unidades de negocio.
     *
     * @return HasMany
     */
    public function unes(): HasMany
    {
        return $this->hasMany(Une::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Estructura.
     * Un estado puede tener muchas estructuras.
     *
     * @return HasMany
     */
    public function estructuras(): HasMany
    {
        return $this->hasMany(Estructura::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Franquicia.
     * Un estado puede tener muchas franquicias.
     *
     * @return HasMany
     */
    public function franquicias(): HasMany
    {
        return $this->hasMany(Franquicia::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Zona.
     * Un estado puede tener muchas zonas.
     *
     * @return HasMany
     */
    public function zonas(): HasMany
    {
        return $this->hasMany(Zona::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Empleado.
     * Un estado puede tener muchos empleados.
     *
     * @return HasMany
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo ZonaEmp.
     * Un estado puede tener muchas asignaciones zona-empleado.
     *
     * @return HasMany
     */
    public function zonasEmpleados(): HasMany
    {
        return $this->hasMany(ZonaEmp::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Geosegmento.
     * Un estado puede tener muchos geosegmentos.
     *
     * @return HasMany
     */
    public function geosegmentos(): HasMany
    {
        return $this->hasMany(Geosegmento::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo ZonaGeo.
     * Un estado puede tener muchas asignaciones zona-geosegmento.
     *
     * @return HasMany
     */
    public function zonasGeosegmentos(): HasMany
    {
        return $this->hasMany(ZonaGeo::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Marca.
     * Un estado puede tener muchas marcas.
     *
     * @return HasMany
     */
    public function marcas(): HasMany
    {
        return $this->hasMany(Marca::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Mercado.
     * Un estado puede tener muchos mercados.
     *
     * @return HasMany
     */
    public function mercados(): HasMany
    {
        return $this->hasMany(Mercado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo MarcaMkt.
     * Un estado puede tener muchas marcas-mercado.
     *
     * @return HasMany
     */
    public function marcasMercados(): HasMany
    {
        return $this->hasMany(MarcaMkt::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Mixta.
     * Un estado puede tener muchas mixtas.
     *
     * @return HasMany
     */
    public function mixtas(): HasMany
    {
        return $this->hasMany(Mixta::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Linea.
     * Un estado puede tener muchas líneas.
     *
     * @return HasMany
     */
    public function lineas(): HasMany
    {
        return $this->hasMany(Linea::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Promocion.
     * Un estado puede tener muchas promociones.
     *
     * @return HasMany
     */
    public function promociones(): HasMany
    {
        return $this->hasMany(Promocion::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo Producto.
     * Un estado puede tener muchos productos.
     *
     * @return HasMany
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con el modelo FuerzaVenta.
     * Un estado puede tener muchas fuerzas de venta.
     *
     * @return HasMany
     */
    public function fuerzasVenta(): HasMany
    {
        return $this->hasMany(FuerzaVenta::class, 'idEstado', 'idEstado');
    }

    /**
     * Scope para filtrar estados activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * Relación con el modelo Ciclo.
     * Un estado puede tener muchos ciclos.
     *
     * @return HasMany
     */
    public function ciclos(): HasMany
    {
        return $this->hasMany(Ciclo::class, 'idEstado', 'idEstado');
    }

    /**
     * Scope para filtrar estados inactivos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactivo($query)
    {
        return $query->where('estado', 'Inactivo');
    }
}

