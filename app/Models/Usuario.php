<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ODS.TAB_USUARIO';

    /**
     * La clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'idUsuario';

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
        'correo',
        'rol',
    ];

    /**
     * Verificar si el usuario es administrador
     *
     * @return bool
     */
    public function esAdministrador(): bool
    {
        return strtolower($this->rol) === 'admin' || strtolower($this->rol) === 'administrador';
    }

    /**
     * Verificar si el usuario tiene un rol específico
     *
     * @param string $rol
     * @return bool
     */
    public function tieneRol(string $rol): bool
    {
        return strtolower($this->rol) === strtolower($rol);
    }
}
