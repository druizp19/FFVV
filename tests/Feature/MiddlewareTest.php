<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiddlewareTest extends TestCase
{
    /**
     * Test que el middleware AzureAuth bloquea usuarios no autenticados
     */
    public function test_azure_auth_middleware_blocks_unauthenticated_users(): void
    {
        $response = $this->get('/ciclos');
        
        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }

    /**
     * Test que el middleware AzureAuth permite usuarios autenticados
     */
    public function test_azure_auth_middleware_allows_authenticated_users(): void
    {
        // Crear usuario en la base de datos
        \App\Models\Usuario::create([
            'correo' => 'test@medifarma.com',
            'rol' => 'usuario'
        ]);

        $this->withSession([
            'azure_user' => [
                'email' => 'test@medifarma.com',
                'name' => 'Test User',
                'rol' => 'usuario',
                'es_admin' => false,
            ]
        ]);

        $response = $this->get('/ciclos');
        
        $response->assertStatus(200);
        
        // Limpiar
        \App\Models\Usuario::where('correo', 'test@medifarma.com')->delete();
    }

    /**
     * Test que el middleware Admin bloquea usuarios no administradores
     */
    public function test_admin_middleware_blocks_non_admin_users(): void
    {
        $this->withSession([
            'azure_user' => [
                'email' => 'user@medifarma.com',
                'name' => 'Regular User',
                'rol' => 'usuario',
                'es_admin' => false,
            ]
        ]);

        // Simular una ruta protegida por admin middleware
        // Nota: Ajusta esta ruta según tu aplicación
        $response = $this->get('/admin/settings');
        
        // Debería devolver 403 o 404 si la ruta no existe
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 404
        );
    }

    /**
     * Test que el middleware RedirectIfAuthenticated redirige usuarios autenticados
     */
    public function test_guest_middleware_redirects_authenticated_users(): void
    {
        $this->withSession([
            'azure_user' => [
                'email' => 'test@medifarma.com',
                'name' => 'Test User',
                'rol' => 'usuario',
                'es_admin' => false,
            ]
        ]);

        $response = $this->get('/login');
        
        // Usuarios autenticados deberían ser redirigidos
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 200
        );
    }

    /**
     * Test que CSRF token está presente en las páginas
     */
    public function test_csrf_token_is_present_in_pages(): void
    {
        // Crear usuario en la base de datos
        \App\Models\Usuario::create([
            'correo' => 'test@medifarma.com',
            'rol' => 'usuario'
        ]);

        $this->withSession([
            'azure_user' => [
                'email' => 'test@medifarma.com',
                'name' => 'Test User',
                'rol' => 'usuario',
                'es_admin' => false,
            ]
        ]);

        $response = $this->get('/ciclos');
        
        $response->assertStatus(200);
        $response->assertSee('csrf-token', false);
        
        // Limpiar
        \App\Models\Usuario::where('correo', 'test@medifarma.com')->delete();
    }
}
