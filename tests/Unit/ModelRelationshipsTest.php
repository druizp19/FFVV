<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ciclo;
use App\Models\Empleado;
use App\Models\Zona;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Geosegmento;

class ModelRelationshipsTest extends TestCase
{
    /**
     * Test que el modelo Ciclo tiene las relaciones esperadas
     */
    public function test_ciclo_model_has_expected_relationships(): void
    {
        $ciclo = new Ciclo();
        
        $this->assertTrue(method_exists($ciclo, 'zonasEmpleados'));
        $this->assertTrue(method_exists($ciclo, 'zonasGeosegmentos'));
        $this->assertTrue(method_exists($ciclo, 'productos'));
        $this->assertTrue(method_exists($ciclo, 'fuerzasVenta'));
        $this->assertTrue(method_exists($ciclo, 'estado'));
    }

    /**
     * Test que el modelo Ciclo tiene los scopes esperados
     */
    public function test_ciclo_model_has_expected_scopes(): void
    {
        $this->assertTrue(method_exists(Ciclo::class, 'scopeAbiertos'));
        $this->assertTrue(method_exists(Ciclo::class, 'scopeCerrados'));
    }

    /**
     * Test que el modelo Ciclo tiene los métodos de cálculo
     */
    public function test_ciclo_model_has_calculation_methods(): void
    {
        $ciclo = new Ciclo();
        
        $this->assertTrue(method_exists($ciclo, 'calcularDiasHabiles'));
    }

    /**
     * Test que el modelo Empleado tiene las relaciones esperadas
     */
    public function test_empleado_model_has_expected_relationships(): void
    {
        $empleado = new Empleado();
        
        $this->assertTrue(method_exists($empleado, 'cargo'));
        $this->assertTrue(method_exists($empleado, 'area'));
        $this->assertTrue(method_exists($empleado, 'estado'));
    }

    /**
     * Test que el modelo Zona tiene las relaciones esperadas
     */
    public function test_zona_model_has_expected_relationships(): void
    {
        $zona = new Zona();
        
        $this->assertTrue(method_exists($zona, 'estado'));
        $this->assertTrue(method_exists($zona, 'zonasEmpleados'));
        $this->assertTrue(method_exists($zona, 'zonasGeosegmentos'));
    }

    /**
     * Test que el modelo Producto tiene las relaciones esperadas
     */
    public function test_producto_model_has_expected_relationships(): void
    {
        $producto = new Producto();
        
        $this->assertTrue(method_exists($producto, 'ciclo'));
        $this->assertTrue(method_exists($producto, 'franqLinea'));
        $this->assertTrue(method_exists($producto, 'marcaMkt'));
        $this->assertTrue(method_exists($producto, 'core'));
    }

    /**
     * Test que el modelo Usuario tiene los métodos esperados
     */
    public function test_usuario_model_has_expected_methods(): void
    {
        $usuario = new Usuario();
        
        $this->assertTrue(method_exists($usuario, 'esAdministrador'));
        $this->assertTrue(method_exists($usuario, 'tieneRol'));
    }

    /**
     * Test que el modelo Usuario verifica correctamente el rol de administrador
     */
    public function test_usuario_verifies_admin_role_correctly(): void
    {
        $usuario = new Usuario(['rol' => 'admin']);
        $this->assertTrue($usuario->esAdministrador());

        $usuario = new Usuario(['rol' => 'ADMIN']);
        $this->assertTrue($usuario->esAdministrador());

        $usuario = new Usuario(['rol' => 'administrador']);
        $this->assertTrue($usuario->esAdministrador());

        $usuario = new Usuario(['rol' => 'usuario']);
        $this->assertFalse($usuario->esAdministrador());
    }

    /**
     * Test que el modelo Usuario verifica roles correctamente
     */
    public function test_usuario_verifies_roles_correctly(): void
    {
        $usuario = new Usuario(['rol' => 'admin']);
        
        $this->assertTrue($usuario->tieneRol('admin'));
        $this->assertTrue($usuario->tieneRol('ADMIN'));
        $this->assertFalse($usuario->tieneRol('usuario'));
    }

    /**
     * Test que los modelos tienen las tablas correctas configuradas
     */
    public function test_models_have_correct_table_names(): void
    {
        $this->assertEquals('ODS.TAB_CICLO', (new Ciclo())->getTable());
        $this->assertEquals('ODS.TAB_EMPLEADO', (new Empleado())->getTable());
        $this->assertEquals('ODS.TAB_ZONA', (new Zona())->getTable());
        $this->assertEquals('ODS.TAB_PRODUCTO', (new Producto())->getTable());
        $this->assertEquals('ODS.TAB_USUARIO_FFVV', (new Usuario())->getTable());
        $this->assertEquals('ODS.TAB_GEOSEGMENTO', (new Geosegmento())->getTable());
    }

    /**
     * Test que los modelos tienen las claves primarias correctas
     */
    public function test_models_have_correct_primary_keys(): void
    {
        $this->assertEquals('idCiclo', (new Ciclo())->getKeyName());
        $this->assertEquals('idEmpleado', (new Empleado())->getKeyName());
        $this->assertEquals('idZona', (new Zona())->getKeyName());
        $this->assertEquals('idProducto', (new Producto())->getKeyName());
        $this->assertEquals('correo', (new Usuario())->getKeyName());
        $this->assertEquals('idGeosegmento', (new Geosegmento())->getKeyName());
    }

    /**
     * Test que el modelo Usuario no usa auto-incremento
     */
    public function test_usuario_model_does_not_use_auto_increment(): void
    {
        $usuario = new Usuario();
        
        $this->assertFalse($usuario->getIncrementing());
        $this->assertEquals('string', $usuario->getKeyType());
    }

    /**
     * Test que el modelo Ciclo tiene los casts correctos
     */
    public function test_ciclo_model_has_correct_casts(): void
    {
        $ciclo = new Ciclo();
        $casts = $ciclo->getCasts();
        
        $this->assertArrayHasKey('fechaInicio', $casts);
        $this->assertArrayHasKey('fechaFin', $casts);
        $this->assertEquals('date', $casts['fechaInicio']);
        $this->assertEquals('date', $casts['fechaFin']);
    }
}
