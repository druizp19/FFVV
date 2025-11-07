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
            
            $usuario = Usuario::where('correo', $azureUser->getEmail())->first();

            if (!$usuario) {
                return redirect()->route('login')
                    ->with('error', 'No tienes acceso al sistema. Contacta al administrador.');
            }

            if (isset($usuario->idEstado) && $usuario->idEstado != 1) {
                return redirect()->route('login')
                    ->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
            }

            session([
                'azure_user' => [
                    'id' => $usuario->idUsuario,
                    'email' => $azureUser->getEmail(),
                    'name' => $azureUser->getName(),
                    'rol' => $usuario->rol,
                    'es_admin' => $usuario->esAdministrador(),
                ]
            ]);

            return redirect()->intended('/ciclos');

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al iniciar sesión. Intenta nuevamente.');
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $request->session()->forget('azure_user');
        $request->session()->flush();

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada exitosamente.');
    }
}
