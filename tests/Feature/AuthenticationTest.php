<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    /**
     * Test que la página de login es accesible
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test que usuarios no autenticados son redirigidos al login
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/ciclos');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test que usuarios autenticados pueden acceder al dashboard
     */
    public function test_authenticated_users_can_access_dashboard(): void
    {
        // Crear usuario en la base de datos para el test
        \App\Models\Usuario::create([
            'correo' => 'test@medifarma.com',
            'rol' => 'usuario'
        ]);

        // Simular usuario autenticado
        $this->withSession([
            'azure_user' => [
                'email' => 'test@medifarma.com',
                'name' => 'Test User',
                'rol' => 'usuario',
                'es_admin' => false,
            ]
        ]);

        $response = $this->get('/dashboard');
        
        $response->assertStatus(200);
        
        // Limpiar
        \App\Models\Usuario::where('correo', 'test@medifarma.com')->delete();
    }

    /**
     * Test que el logout limpia la sesión
     */
    public function test_logout_clears_session(): void
    {
        // Simular usuario autenticado
        $this->withSession([
            'azure_user' => [
                'email' => 'test@medifarma.com',
                'name' => 'Test User',
                'rol' => 'usuario',
                'es_admin' => false,
            ]
        ]);

        $response = $this->post('/logout');
        
        $response->assertRedirect('/login');
        $response->assertSessionMissing('azure_user');
    }

    /**
     * Test que usuarios invitados no pueden acceder a rutas protegidas
     */
    public function test_guests_cannot_access_protected_routes(): void
    {
        $protectedRoutes = [
            '/ciclos',
            '/empleados',
            '/zonas',
            '/productos',
            '/geosegmentos',
            '/historial',
            '/dashboard',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }
}
