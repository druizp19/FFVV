<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Empleado;

class EmpleadoTest extends TestCase
{
    protected function createAuthenticatedUser()
    {
        Usuario::create([
            'correo' => 'test@medifarma.com',
            'rol' => 'admin'
        ]);

        $this->withSession([
            'azure_user' => [
                'email' => 'test@medifarma.com',
                'name' => 'Test User',
                'rol' => 'admin',
                'es_admin' => true,
            ]
        ]);
    }

    protected function cleanUp()
    {
        Usuario::where('correo', 'test@medifarma.com')->delete();
    }

    /**
     * Test que la página de empleados es accesible
     */
    public function test_empleados_page_is_accessible(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->get('/empleados');
        
        $response->assertStatus(200);
        $response->assertViewIs('empleados.index');

        $this->cleanUp();
    }

    /**
     * Test que requiere autenticación para ver empleados
     */
    public function test_empleados_requires_authentication(): void
    {
        $response = $this->get('/empleados');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test de validación al crear empleado sin datos
     */
    public function test_create_empleado_validation_fails_without_required_fields(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->postJson('/empleados', []);
        
        $response->assertStatus(422);
        // Verificar que hay errores de validación
        $this->assertGreaterThan(0, count($response->json('errors')));

        $this->cleanUp();
    }

    /**
     * Test de validación de email
     */
    public function test_create_empleado_validates_email_format(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->postJson('/empleados', [
            'codEmpleado' => 'EMP001',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'correo' => 'email-invalido',
            'idCargo' => 1,
            'idArea' => 1,
            'idEstado' => 1
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['correo']);

        $this->cleanUp();
    }

    /**
     * Test que el modelo Empleado tiene las relaciones correctas
     */
    public function test_empleado_has_relationships(): void
    {
        $empleado = new Empleado();
        
        $this->assertTrue(method_exists($empleado, 'cargo'));
        $this->assertTrue(method_exists($empleado, 'area'));
        $this->assertTrue(method_exists($empleado, 'estado'));
    }

    /**
     * Test de búsqueda de empleados
     */
    public function test_empleados_search_endpoint_works(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->get('/empleados/search?q=test');
        
        $response->assertStatus(200);

        $this->cleanUp();
    }

    /**
     * Test que obtiene todos los empleados
     */
    public function test_get_all_empleados_endpoint_works(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->get('/empleados/all');
        
        $response->assertStatus(200);

        $this->cleanUp();
    }
}
