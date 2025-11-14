<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZonaRequest extends FormRequest
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
            'zona' => 'required|string|max:100',
            'idCiclo' => 'required|integer|exists:ODS.TAB_CICLO,idCiclo',
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
            'zona.required' => 'El nombre de la zona es obligatorio.',
            'zona.max' => 'El nombre de la zona no puede exceder 100 caracteres.',
            'idCiclo.required' => 'El ciclo es obligatorio.',
            'idCiclo.exists' => 'El ciclo seleccionado no existe.',
            'idEstado.required' => 'El estado es obligatorio.',
            'idEstado.exists' => 'El estado seleccionado no existe.',
        ];
    }
}
