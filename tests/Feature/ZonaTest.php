<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Zona;

class ZonaTest extends TestCase
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
     * Test que la página de zonas es accesible
     */
    public function test_zonas_page_is_accessible(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->get('/zonas');
        
        $response->assertStatus(200);
        $response->assertViewIs('zonas.index');

        $this->cleanUp();
    }

    /**
     * Test que requiere autenticación para ver zonas
     */
    public function test_zonas_requires_authentication(): void
    {
        $response = $this->get('/zonas');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test de validación al crear zona sin datos
     */
    public function test_create_zona_validation_fails_without_required_fields(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->postJson('/zonas', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['zona']);

        $this->cleanUp();
    }

    /**
     * Test que el modelo Zona tiene las relaciones correctas
     */
    public function test_zona_has_relationships(): void
    {
        $zona = new Zona();
        
        $this->assertTrue(method_exists($zona, 'estado'));
        $this->assertTrue(method_exists($zona, 'zonasEmpleados'));
        $this->assertTrue(method_exists($zona, 'zonasGeosegmentos'));
    }

    /**
     * Test que el modelo Zona tiene los atributos fillable
     */
    public function test_zona_has_fillable_attributes(): void
    {
        $zona = new Zona();
        $fillable = $zona->getFillable();
        
        $this->assertContains('zona', $fillable);
        $this->assertContains('idEstado', $fillable);
    }

    /**
     * Test que el modelo Zona usa la tabla correcta
     */
    public function test_zona_uses_correct_table(): void
    {
        $zona = new Zona();
        
        $this->assertEquals('ODS.TAB_ZONA', $zona->getTable());
    }

    /**
     * Test que el modelo Zona tiene la clave primaria correcta
     */
    public function test_zona_has_correct_primary_key(): void
    {
        $zona = new Zona();
        
        $this->assertEquals('idZona', $zona->getKeyName());
    }

    /**
     * Test que las rutas de API de zonas funcionan
     */
    public function test_zona_api_endpoints_are_accessible(): void
    {
        $this->createAuthenticatedUser();

        // Test endpoint de empleados de una zona (asumiendo que existe zona con id 1)
        $response = $this->get('/zonas/1/empleados');
        $this->assertContains($response->status(), [200, 404]); // 404 si no existe la zona

        // Test endpoint de geosegmentos de una zona
        $response = $this->get('/zonas/1/geosegmentos');
        $this->assertContains($response->status(), [200, 404]);

        $this->cleanUp();
    }
}
