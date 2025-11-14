<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test que la ruta raíz redirige correctamente
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // La ruta raíz redirige al login si no estás autenticado
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
