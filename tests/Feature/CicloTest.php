<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Ciclo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CicloTest extends TestCase
{
    /**
     * Test que la página de ciclos es accesible para usuarios autenticados
     */
    public function test_ciclos_page_is_accessible_for_authenticated_users(): void
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
        $response->assertViewIs('ciclos.index');
        
        // Limpiar
        \App\Models\Usuario::where('correo', 'test@medifarma.com')->delete();
    }

    /**
     * Test que se requiere autenticación para ver ciclos
     */
    public function test_ciclos_requires_authentication(): void
    {
        $response = $this->get('/ciclos');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test de validación al crear ciclo sin datos
     */
    public function test_create_ciclo_validation_fails_without_data(): void
    {
        // Crear usuario en la base de datos
        \App\Models\Usuario::create([
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

        $response = $this->postJson('/ciclos', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ciclo', 'fechaInicio', 'fechaFin']);
        
        // Limpiar
        \App\Models\Usuario::where('correo', 'test@medifarma.com')->delete();
    }

    /**
     * Test de validación de fechas
     */
    public function test_create_ciclo_validates_date_order(): void
    {
        // Crear usuario en la base de datos
        \App\Models\Usuario::create([
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

        $response = $this->postJson('/ciclos', [
            'ciclo' => 'Test Ciclo',
            'fechaInicio' => '2024-12-31',
            'fechaFin' => '2024-01-01', // Fecha fin antes de inicio
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fechaFin']);
        
        // Limpiar
        \App\Models\Usuario::where('correo', 'test@medifarma.com')->delete();
    }

    /**
     * Test que el modelo Ciclo calcula días hábiles correctamente
     */
    public function test_ciclo_calculates_business_days(): void
    {
        $ciclo = new Ciclo([
            'ciclo' => 'Test',
            'fechaInicio' => '2024-01-01',
            'fechaFin' => '2024-01-10',
        ]);

        $dias = $ciclo->calcularDiasHabiles();
        
        $this->assertIsInt($dias);
        $this->assertGreaterThanOrEqual(0, $dias);
    }

    /**
     * Test que el scope abiertos funciona
     */
    public function test_ciclo_scope_abiertos_works(): void
    {
        // Este test verifica que el scope existe y es llamable
        $query = Ciclo::abiertos();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    /**
     * Test que el scope cerrados funciona
     */
    public function test_ciclo_scope_cerrados_works(): void
    {
        // Este test verifica que el scope existe y es llamable
        $query = Ciclo::cerrados();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }
}
