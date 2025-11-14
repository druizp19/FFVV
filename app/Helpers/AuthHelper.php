<?php

if (!function_exists('currentUser')) {
    /**
     * Obtener el usuario autenticado (SSO o Azure)
     */
    function currentUser()
    {
        // Priorizar sesión SSO, luego Azure
        return session('usuario') ?? session('azure_user');
    }
}

if (!function_exists('azureUser')) {
    /**
     * Obtener el usuario autenticado de Azure
     * @deprecated Usar currentUser() en su lugar
     */
    function azureUser()
    {
        return session('azure_user');
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Verificar si el usuario es administrador
     */
    function isAdmin(): bool
    {
        // Verificar sesión SSO primero
        if (session()->has('rol')) {
            $rol = session('rol');
            return $rol && (strtoupper($rol['rol'] ?? '') === 'ADMINISTRADOR');
        }
        
        // Verificar sesión Azure
        $user = azureUser();
        return $user && ($user['es_admin'] ?? false);
    }
}

if (!function_exists('userName')) {
    /**
     * Obtener el nombre del usuario autenticado
     */
    function userName(): string
    {
        // Verificar sesión SSO primero
        if (session()->has('usuario')) {
            $usuario = session('usuario');
            return $usuario['nombreCompleto'] ?? $usuario['usuario'] ?? 'Usuario';
        }
        
        // Verificar sesión Azure
        $user = azureUser();
        return $user['name'] ?? 'Usuario';
    }
}

if (!function_exists('userEmail')) {
    /**
     * Obtener el email del usuario autenticado
     */
    function userEmail(): string
    {
        // Verificar sesión SSO primero
        if (session()->has('usuario')) {
            $usuario = session('usuario');
            return $usuario['correo'] ?? '';
        }
        
        // Verificar sesión Azure
        $user = azureUser();
        return $user['email'] ?? '';
    }
}

if (!function_exists('userRole')) {
    /**
     * Obtener el rol del usuario autenticado
     */
    function userRole(): string
    {
        // Verificar sesión SSO primero
        if (session()->has('rol')) {
            $rol = session('rol');
            return is_array($rol) ? ($rol['rol'] ?? 'Usuario') : 'Usuario';
        }
        
        // Verificar sesión Azure
        $user = azureUser();
        return is_array($user) ? ($user['rol'] ?? 'Usuario') : 'Usuario';
    }
}
