<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Usuario;

class AzureAuthMiddleware
{
    /**
     * Verifica que el usuario esté autenticado con Azure AD
     * y que su acceso siga siendo válido.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si existe sesión (Azure o SSO)
        if (!session()->has('azure_user') && !session()->has('usuario')) {
            \Log::info('AzureAuthMiddleware: Sin sesión, redirigiendo a login', [
                'url' => $request->fullUrl(),
                'has_azure' => session()->has('azure_user'),
                'has_usuario' => session()->has('usuario'),
            ]);
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder al sistema.');
        }

        // Si es sesión SSO, permitir el acceso sin validaciones adicionales
        // (las validaciones ya se hicieron al crear la sesión)
        if (session()->has('usuario') && !session()->has('azure_user')) {
            \Log::info('AzureAuthMiddleware: Sesión SSO detectada, permitiendo acceso', [
                'usuario' => session('usuario.usuario'),
                'correo' => session('usuario.correo'),
            ]);
            return $next($request);
        }

        // Validación para sesión Azure (código original)
        $azureUser = session('azure_user');

        // Verificar que el correo del usuario siga estando autorizado en la base de datos
        $usuario = Usuario::where('correo', $azureUser['email'])->first();

        if (!$usuario) {
            // El usuario fue eliminado o su acceso fue revocado
            session()->forget('azure_user');
            session()->flush();
            
            return redirect()->route('login')
                ->with('error', 'Tu acceso ha sido revocado. Contacta al administrador del sistema.');
        }

        // Actualizar el rol en sesión si cambió en la base de datos
        if ($usuario->rol !== $azureUser['rol']) {
            session(['azure_user.rol' => $usuario->rol]);
            session(['azure_user.es_admin' => $usuario->esAdministrador()]);
        }

        return $next($request);
    }
}
