<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Secret Key
    |--------------------------------------------------------------------------
    |
    | Esta clave se utiliza para firmar y validar los tokens SSO.
    | Debe ser la misma en todos los sistemas que participen en el SSO.
    |
    */

    'secret_key' => env('SSO_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | SSO Portal URL
    |--------------------------------------------------------------------------
    |
    | URL del portal principal SSO. Se utiliza para redirigir al usuario
    | cuando cierra sesión desde un sistema secundario.
    |
    */

    'portal_url' => env('SSO_PORTAL_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Token Expiration Time
    |--------------------------------------------------------------------------
    |
    | Tiempo de expiración de los tokens SSO en segundos.
    | Por defecto: 300 segundos (5 minutos)
    |
    */

    'token_expiration' => env('SSO_TOKEN_EXPIRATION', 300),

];
