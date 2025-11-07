<?php

if (!function_exists('azureUser')) {
    /**
     * Obtener el usuario autenticado de Azure
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
        $user = azureUser();
        return $user['email'] ?? '';
    }
}
