<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Verifica que el usuario autenticado tenga permisos de administrador.
     * Este middleware debe usarse después de AzureAuthMiddleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que exista sesión
        if (!session()->has('azure_user')) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder.');
        }

        // Obtener usuario de la sesión
        $user = session('azure_user');
        
        // Verificar permisos de administrador
        if (!($user['es_admin'] ?? false)) {
            // Si es petición AJAX, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos de administrador para realizar esta acción.'
                ], 403);
            }
            
            // Si es petición normal, abortar con error 403
            abort(403, 'No tienes permisos de administrador para acceder a esta sección.');
        }

        return $next($request);
    }
}
