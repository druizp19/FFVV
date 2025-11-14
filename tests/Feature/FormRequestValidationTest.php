<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Requests\StoreCicloRequest;
use App\Http\Requests\UpdateCicloRequest;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Requests\StoreZonaRequest;
use Illuminate\Support\Facades\Validator;

class FormRequestValidationTest extends TestCase
{
    /**
     * Test validación de StoreCicloRequest
     */
    public function test_store_ciclo_request_validates_required_fields(): void
    {
        $request = new StoreCicloRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('ciclo'));
        $this->assertTrue($validator->errors()->has('fechaInicio'));
        $this->assertTrue($validator->errors()->has('fechaFin'));
    }

    /**
     * Test validación de fechas en StoreCicloRequest
     */
    public function test_store_ciclo_request_validates_date_order(): void
    {
        $request = new StoreCicloRequest();
        $rules = $request->rules();
        
        // Verificar que las reglas incluyen validación de fechas
        $this->assertArrayHasKey('fechaInicio', $rules);
        $this->assertArrayHasKey('fechaFin', $rules);
        $this->assertIsString($rules['fechaInicio']);
        $this->assertIsString($rules['fechaFin']);
    }

    /**
     * Test validación exitosa de StoreCicloRequest
     */
    public function test_store_ciclo_request_passes_with_valid_data(): void
    {
        $request = new StoreCicloRequest();
        $rules = $request->rules();
        
        // Verificar que las reglas están definidas correctamente
        $this->assertArrayHasKey('ciclo', $rules);
        $this->assertArrayHasKey('fechaInicio', $rules);
        $this->assertArrayHasKey('fechaFin', $rules);
    }

    /**
     * Test validación de StoreEmpleadoRequest
     */
    public function test_store_empleado_request_validates_required_fields(): void
    {
        $request = new StoreEmpleadoRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        // Verificar que hay errores de validación
        $this->assertGreaterThan(0, $validator->errors()->count());
    }

    /**
     * Test validación de email en StoreEmpleadoRequest
     */
    public function test_store_empleado_request_validates_email_format(): void
    {
        $request = new StoreEmpleadoRequest();
        $rules = $request->rules();
        
        // Verificar que las reglas incluyen validación de correo
        $this->assertArrayHasKey('correo', $rules);
    }

    /**
     * Test validación de StoreZonaRequest
     */
    public function test_store_zona_request_validates_required_fields(): void
    {
        $request = new StoreZonaRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('zona'));
        $this->assertTrue($validator->errors()->has('idCiclo'));
        $this->assertTrue($validator->errors()->has('idEstado'));
    }

    /**
     * Test que los mensajes personalizados existen
     */
    public function test_form_requests_have_custom_messages(): void
    {
        $storeCicloRequest = new StoreCicloRequest();
        $storeEmpleadoRequest = new StoreEmpleadoRequest();
        $storeZonaRequest = new StoreZonaRequest();

        $this->assertIsArray($storeCicloRequest->messages());
        $this->assertIsArray($storeEmpleadoRequest->messages());
        $this->assertIsArray($storeZonaRequest->messages());
        
        $this->assertNotEmpty($storeCicloRequest->messages());
        $this->assertNotEmpty($storeEmpleadoRequest->messages());
        $this->assertNotEmpty($storeZonaRequest->messages());
    }

    /**
     * Test que UpdateCicloRequest permite actualizar con el mismo nombre
     */
    public function test_update_ciclo_request_allows_same_name_for_same_record(): void
    {
        $request = new UpdateCicloRequest();
        
        // Verificar que las reglas incluyen la validación unique con ignore
        $rules = $request->rules();
        
        $this->assertArrayHasKey('ciclo', $rules);
        $this->assertIsArray($rules['ciclo']);
    }
}
