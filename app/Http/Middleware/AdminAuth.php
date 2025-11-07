<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = session('azure_user');

        if (!$user || !$user['es_admin']) {
            return redirect()->back()
                ->with('error', 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }
}
