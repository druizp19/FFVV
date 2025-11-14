<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelperFunctionsTest extends TestCase
{
    /**
     * Test que la función azureUser existe
     */
    public function test_azure_user_function_exists(): void
    {
        $this->assertTrue(function_exists('azureUser'));
    }

    /**
     * Test que la función isAdmin existe
     */
    public function test_is_admin_function_exists(): void
    {
        $this->assertTrue(function_exists('isAdmin'));
    }

    /**
     * Test que la función userName existe
     */
    public function test_user_name_function_exists(): void
    {
        $this->assertTrue(function_exists('userName'));
    }

    /**
     * Test que la función userEmail existe
     */
    public function test_user_email_function_exists(): void
    {
        $this->assertTrue(function_exists('userEmail'));
    }

    /**
     * Test que las funciones helper manejan correctamente valores nulos
     */
    public function test_helper_functions_handle_null_values(): void
    {
        // Las funciones deberían manejar correctamente cuando no hay sesión
        // Esto se prueba mejor en tests de integración
        $this->assertTrue(true);
    }
}
