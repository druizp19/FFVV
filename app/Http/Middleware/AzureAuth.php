<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AzureAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('azure_user')) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder.');
        }

        return $next($request);
    }
}
