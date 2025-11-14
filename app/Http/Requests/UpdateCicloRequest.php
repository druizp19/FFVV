<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCicloRequest extends FormRequest
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
        $cicloId = $this->route('id');

        return [
            'ciclo' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ODS.TAB_CICLO', 'ciclo')->ignore($cicloId, 'idCiclo')
            ],
            'fechaInicio' => 'required|date|before_or_equal:fechaFin',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
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
            'ciclo.required' => 'El nombre del ciclo es obligatorio.',
            'ciclo.unique' => 'Ya existe otro ciclo con este nombre.',
            'ciclo.max' => 'El nombre del ciclo no puede exceder 100 caracteres.',
            'fechaInicio.required' => 'La fecha de inicio es obligatoria.',
            'fechaInicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fechaInicio.before_or_equal' => 'La fecha de inicio debe ser anterior o igual a la fecha de fin.',
            'fechaFin.required' => 'La fecha de fin es obligatoria.',
            'fechaFin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fechaFin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}
