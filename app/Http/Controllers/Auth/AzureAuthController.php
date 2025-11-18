<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AzureAuthController extends Controller
{
    /**
     * Redirigir al usuario a la página de autenticación de Azure
     */
    public function redirectToAzure()
    {
        return Socialite::driver('azure')
            ->stateless()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Manejar el callback de Azure
     */
    public function handleAzureCallback()
    {
        try {
            $azureUser = Socialite::driver('azure')->stateless()->user();
            
            // Buscar usuario primero en TAB_USUARIO (sistema general)
            $usuario = \DB::table('ODS.TAB_USUARIO_FFVV')
                ->where('correo', $azureUser->getEmail())
                ->first();

            $usuarioRol = null;
            $esUsuarioFFVV = false;

            if ($usuario) {
                // Usuario encontrado en sistema general, verificar acceso a FUERZA DE VENTA
                $sistema = \DB::table('ODS.TAB_SISTEMA')
                    ->where('sistema', 'FUERZA DE VENTA')
                    ->first();

                if ($sistema) {
                    $usuarioRol = \DB::table('ODS.TAB_USUARIO_ROL as ur')
                        ->join('ODS.TAB_ROL as r', 'ur.idRol', '=', 'r.idRol')
                        ->where('ur.idUsuario', $usuario->idUsuario)
                        ->where('ur.idSistema', $sistema->idSistema)
                        ->select('ur.idUsuarioRol', 'ur.idRol', 'r.rol')
                        ->first();
                }
            }

            // Si no se encontró en TAB_USUARIO o no tiene acceso, buscar en TAB_USUARIO_FFVV
            if (!$usuarioRol) {
                $usuarioFFVV = \DB::table('ODS.TAB_USUARIO_FFVV')
                    ->where('correo', $azureUser->getEmail())
                    ->first();

                if ($usuarioFFVV) {
                    // Usuario encontrado en tabla específica de FFVV
                    $esUsuarioFFVV = true;
                    
                    // Crear objeto usuario compatible
                    $usuario = (object)[
                        'idUsuario' => null,
                        'usuario' => explode('@', $usuarioFFVV->correo)[0],
                        'correo' => $usuarioFFVV->correo,
                    ];
                    
                    // Crear objeto rol compatible
                    $usuarioRol = (object)[
                        'idUsuarioRol' => null,
                        'idRol' => null,
                        'rol' => strtoupper($usuarioFFVV->rol),
                    ];
                    
                    \Log::info('Usuario FFVV encontrado', [
                        'correo' => $usuarioFFVV->correo,
                        'rol' => $usuarioFFVV->rol
                    ]);
                }
            }

            // Si no se encontró en ninguna tabla
            if (!$usuarioRol) {
                \Log::warning('Usuario Azure no encontrado en ninguna tabla', ['correo' => $azureUser->getEmail()]);
                return redirect()->route('login')
                    ->with('error', 'No tienes acceso al sistema. Tu correo no está autorizado. Contacta al administrador.');
            }

            // Obtener información adicional del empleado si existe (por correo)
            $empleado = \DB::table('ODS.TAB_EMPLEADO')
                ->where('correo', $usuario->correo)
                ->where('idEstado', 1)
                ->first();

            // Crear sesión del usuario (mismo formato que SSO)
            session()->put('usuario', [
                'idUsuario' => $usuario->idUsuario,
                'usuario' => $usuario->usuario,
                'correo' => $usuario->correo,
                'idRol' => $usuarioRol->idRol,
                'idEmpleado' => $empleado->idEmpleado ?? null,
                'nombreCompleto' => $empleado ? trim(($empleado->nombre ?? '') . ' ' . ($empleado->apeNombre ?? '')) : $usuario->usuario,
                'azure_login' => true, // Marcar que fue login Azure
            ]);

            // Guardar información del rol
            session()->put('rol', [
                'idRol' => $usuarioRol->idRol,
                'rol' => $usuarioRol->rol,
            ]);

            // También mantener el formato anterior para compatibilidad
            session()->put('azure_user', [
                'email' => $azureUser->getEmail(),
                'name' => $azureUser->getName(),
                'rol' => $usuarioRol->rol,
                'es_admin' => $usuarioRol->rol === 'ADMINISTRADOR',
            ]);

            \Log::info('Login Azure exitoso', [
                'usuario' => $usuario->usuario,
                'correo' => $usuario->correo,
            ]);

            // Registrar en historial
            try {
                $cicloActivo = \DB::table('ODS.TAB_CICLO')
                    ->whereRaw('GETDATE() BETWEEN fechaInicio AND fechaFin')
                    ->first();

                \DB::table('ODS.TAB_HISTORIAL')->insert([
                    'idCiclo' => $cicloActivo->idCiclo ?? null,
                    'accion' => 'Login Azure',
                    'entidad' => 'Usuario',
                    'idEntidad' => $usuario->idUsuario,
                    'descripcion' => 'Acceso al sistema FUERZA DE VENTA mediante Azure AD',
                    'datosNuevos' => json_encode([
                        'sistema' => 'FUERZA DE VENTA',
                        'rol' => $usuarioRol->rol,
                        'metodo' => 'Azure AD',
                    ]),
                    'usuario' => $usuario->usuario,
                    'fechaHora' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::debug('No se pudo registrar en historial: ' . $e->getMessage());
            }

            return redirect()->intended(route('dashboard.index'));

        } catch (\Exception $e) {
            \Log::error('Error en Azure callback: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->with('error', 'Error al iniciar sesión con Microsoft. Intenta nuevamente.');
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        // Verificar si la sesión fue creada por SSO ANTES de limpiar
        $isSSOLogin = session('usuario.sso_auto_login', false);
        $usuario = session('usuario.usuario', 'unknown');
        
        \Log::info('Logout iniciado', [
            'usuario' => $usuario,
            'is_sso' => $isSSOLogin,
            'has_usuario' => session()->has('usuario'),
            'has_azure' => session()->has('azure_user'),
        ]);
        
        // Limpiar toda la sesión
        $request->session()->forget('azure_user');
        $request->session()->forget('usuario');
        $request->session()->forget('rol');
        $request->session()->flush();

        // Si fue login SSO, redirigir al logout del portal
        if ($isSSOLogin) {
            $portalUrl = config('sso.portal_url', env('SSO_PORTAL_URL', 'http://localhost:8000'));
            // Redirigir a la ruta de logout SSO del portal
            $logoutUrl = rtrim($portalUrl, '/') . '/logout-sso';
            \Log::info('Redirigiendo al logout SSO del portal', ['url' => $logoutUrl]);
            return redirect()->away($logoutUrl);
        }

        // Si fue login Azure, redirigir al login local
        \Log::info('Redirigiendo al login local después de logout');
        return redirect()->route('login')
            ->with('success', 'Sesión cerrada exitosamente.');
    }
}
