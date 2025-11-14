<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmpleadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Permitir si el usuario está autenticado
        return session()->has('azure_user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codEmpleado' => 'required|string|max:50|unique:ODS.TAB_EMPLEADO,codEmpleado',
            'nombre' => 'required|string|max:200',
            'apellido' => 'required|string|max:200',
            'correo' => 'nullable|email|max:200',
            'telefono' => 'nullable|string|max:20',
            'idCargo' => 'required|integer|exists:ODS.TAB_CARGO,idCargo',
            'idArea' => 'required|integer|exists:ODS.TAB_AREA,idArea',
            'idEstado' => 'required|integer|exists:ODS.TAB_ESTADO,idEstado',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codEmpleado.required' => 'El código del empleado es obligatorio.',
            'codEmpleado.unique' => 'Ya existe un empleado con este código.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'correo.email' => 'El correo debe ser una dirección válida.',
            'idCargo.required' => 'El cargo es obligatorio.',
            'idCargo.exists' => 'El cargo seleccionado no existe.',
            'idArea.required' => 'El área es obligatoria.',
            'idArea.exists' => 'El área seleccionada no existe.',
            'idEstado.required' => 'El estado es obligatorio.',
            'idEstado.exists' => 'El estado seleccionado no existe.',
        ];
    }
}
