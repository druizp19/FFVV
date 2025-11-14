<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Producto;

class ProductoTest extends TestCase
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
     * Test que la página de productos es accesible
     */
    public function test_productos_page_is_accessible(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->get('/productos');
        
        $response->assertStatus(200);
        $response->assertViewIs('productos.index');

        $this->cleanUp();
    }

    /**
     * Test que requiere autenticación para ver productos
     */
    public function test_productos_requires_authentication(): void
    {
        $response = $this->get('/productos');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test que el endpoint de todos los productos funciona
     */
    public function test_get_all_productos_endpoint_works(): void
    {
        $this->createAuthenticatedUser();

        $response = $this->get('/productos/all');
        
        $response->assertStatus(200);

        $this->cleanUp();
    }

    /**
     * Test que el modelo Producto tiene las relaciones correctas
     */
    public function test_producto_has_relationships(): void
    {
        $producto = new Producto();
        
        $this->assertTrue(method_exists($producto, 'ciclo'));
        $this->assertTrue(method_exists($producto, 'franqLinea'));
        $this->assertTrue(method_exists($producto, 'marcaMkt'));
    }

    /**
     * Test que el modelo Producto tiene los atributos fillable
     */
    public function test_producto_has_fillable_attributes(): void
    {
        $producto = new Producto();
        $fillable = $producto->getFillable();
        
        $this->assertContains('idCiclo', $fillable);
        $this->assertContains('idFranqLinea', $fillable);
        $this->assertContains('idMarcaMkt', $fillable);
    }

    /**
     * Test que el modelo Producto usa la tabla correcta
     */
    public function test_producto_uses_correct_table(): void
    {
        $producto = new Producto();
        
        $this->assertEquals('ODS.TAB_PRODUCTO', $producto->getTable());
    }

    /**
     * Test que el modelo Producto tiene la clave primaria correcta
     */
    public function test_producto_has_correct_primary_key(): void
    {
        $producto = new Producto();
        
        $this->assertEquals('idProducto', $producto->getKeyName());
    }
}
