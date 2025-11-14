<?php

namespace App\Http\Middleware;

use App\Services\SSOTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SSOAutoLoginMiddleware
{
    public function __construct(
        private SSOTokenService $ssoTokenService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo procesar si hay un token en la query string
        if ($request->has('token') && !Session::has('usuario') && !Session::has('azure_user')) {
            $token = $request->query('token');
            
            // Validar el token
            $userData = $this->ssoTokenService->validateToken($token);
            
            if ($userData) {
                // Buscar usuario en la base de datos
                $usuario = DB::table('ODS.TAB_USUARIO')
                    ->where('correo', $userData['correo'])
                    ->first();
                
                if ($usuario) {
                    // Obtener el sistema FUERZA DE VENTA
                    $sistema = DB::table('ODS.TAB_SISTEMA')
                        ->where('sistema', 'FUERZA DE VENTA')
                        ->first();
                    
                    if ($sistema) {
                        // Verificar acceso del usuario
                        $usuarioRol = DB::table('ODS.TAB_USUARIO_ROL as ur')
                            ->join('ODS.TAB_ROL as r', 'ur.idRol', '=', 'r.idRol')
                            ->where('ur.idUsuario', $usuario->idUsuario)
                            ->where('ur.idSistema', $sistema->idSistema)
                            ->select('ur.idUsuarioRol', 'ur.idRol', 'r.rol')
                            ->first();
                        
                        if ($usuarioRol) {
                            // Obtener empleado si existe
                            $empleado = DB::table('ODS.TAB_EMPLEADO')
                                ->where('correo', $usuario->correo)
                                ->where('idEstado', 1)
                                ->first();
                            
                            // Crear sesión automáticamente
                            Session::put('usuario', [
                                'idUsuario' => $usuario->idUsuario,
                                'usuario' => $usuario->usuario,
                                'correo' => $usuario->correo,
                                'idRol' => $usuarioRol->idRol,
                                'idEmpleado' => $empleado->idEmpleado ?? null,
                                'nombreCompleto' => $empleado ? trim(($empleado->nombre ?? '') . ' ' . ($empleado->apeNombre ?? '')) : $usuario->usuario,
                                'sso_auto_login' => true,
                            ]);
                            
                            Session::put('rol', [
                                'idRol' => $usuarioRol->idRol,
                                'rol' => $usuarioRol->rol,
                            ]);
                            
                            Log::info('SSO Auto-login exitoso', [
                                'usuario' => $usuario->usuario,
                                'correo' => $usuario->correo,
                            ]);
                            
                            Log::info('SSO: Sesión creada exitosamente', [
                                'session_usuario' => Session::has('usuario'),
                                'session_rol' => Session::has('rol'),
                                'url_destino' => $request->fullUrl(),
                            ]);
                            
                            // Remover el token de la query string para que no aparezca en la URL
                            $request->query->remove('token');
                            
                            // Continuar con la request - la sesión ya está creada
                            // y el AzureAuthMiddleware la reconocerá
                            return $next($request);
                        }
                    }
                }
                
                Log::warning('Token SSO válido pero usuario sin acceso', [
                    'correo' => $userData['correo'] ?? 'unknown'
                ]);
            }
        }
        
        return $next($request);
    }
}
