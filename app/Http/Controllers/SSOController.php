<?php

namespace App\Http\Controllers;

use App\Services\SSOTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SSOController extends Controller
{
    public function __construct(
        private SSOTokenService $ssoTokenService
    ) {}

    /**
     * Maneja el login SSO desde el portal principal
     */
    public function login(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            Log::warning('Intento de login SSO sin token');
            return redirect('/login')->with('error', 'Token de autenticación no proporcionado');
        }

        // Validar el token
        $userData = $this->ssoTokenService->validateToken($token);

        if (!$userData) {
            Log::warning('Token SSO inválido o expirado');
            return redirect('/login')->with('error', 'Token de autenticación inválido o expirado');
        }

        // Buscar usuario en la base de datos por correo
        $usuario = DB::table('ODS.TAB_USUARIO')
            ->where('correo', $userData['correo'])
            ->first();

        if (!$usuario) {
            Log::warning('Usuario SSO no encontrado', ['correo' => $userData['correo']]);
            return redirect('/login')->with('error', 'Usuario no encontrado en el sistema');
        }

        // Obtener el ID del sistema "FUERZA DE VENTA"
        $sistema = DB::table('ODS.TAB_SISTEMA')
            ->where('sistema', 'FUERZA DE VENTA')
            ->first();

        if (!$sistema) {
            Log::error('Sistema FUERZA DE VENTA no encontrado en TAB_SISTEMA');
            return redirect('/login')->with('error', 'Error de configuración del sistema');
        }

        // Verificar que el usuario tenga acceso a este sistema
        $usuarioRol = DB::table('ODS.TAB_USUARIO_ROL as ur')
            ->join('ODS.TAB_ROL as r', 'ur.idRol', '=', 'r.idRol')
            ->where('ur.idUsuario', $usuario->idUsuario)
            ->where('ur.idSistema', $sistema->idSistema)
            ->select('ur.idUsuarioRol', 'ur.idRol', 'r.rol')
            ->first();

        if (!$usuarioRol) {
            Log::warning('Usuario sin acceso al sistema FUERZA DE VENTA', [
                'usuario' => $usuario->usuario,
                'correo' => $usuario->correo
            ]);
            return redirect('/login')->with('error', 'No tienes acceso a este sistema. Contacta al administrador.');
        }

        // Obtener información adicional del empleado si existe (por correo)
        $empleado = DB::table('ODS.TAB_EMPLEADO')
            ->where('correo', $usuario->correo)
            ->where('idEstado', 1)
            ->first();

        // Crear sesión del usuario
        Session::put('usuario', [
            'idUsuario' => $usuario->idUsuario,
            'usuario' => $usuario->usuario,
            'correo' => $usuario->correo,
            'idRol' => $usuarioRol->idRol,
            'idEmpleado' => $empleado->idEmpleado ?? null,
            'nombreCompleto' => $empleado ? trim(($empleado->nombre ?? '') . ' ' . ($empleado->apeNombre ?? '')) : $usuario->usuario,
            'sso_login' => true, // Marcar que fue login SSO
        ]);

        // Guardar información del rol
        Session::put('rol', [
            'idRol' => $usuarioRol->idRol,
            'rol' => $usuarioRol->rol,
        ]);

        Log::info('Login SSO exitoso', [
            'usuario' => $usuario->usuario,
            'correo' => $usuario->correo,
            'ip' => $request->ip()
        ]);

        // Redirigir al dashboard directamente
        return redirect()->route('dashboard.index')->with('success', '¡Bienvenido!');
    }

    /**
     * Cierra la sesión SSO
     */
    public function logout(Request $request)
{
    $usuario = Session::get('usuario');
    if ($usuario) {
        Log::info('Logout SSO', ['usuario' => $usuario['usuario'] ?? 'unknown']);
    }
    
    // Limpiar sesión de FFVV
    Session::flush();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    // Redirigir al portal para cerrar sesión allá también
    $portalUrl = env('SSO_PORTAL_URL', 'http://localhost:8000');
    return redirect($portalUrl . '/logout-sso');
}
}
