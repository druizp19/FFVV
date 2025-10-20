<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CicloRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ciclo' => 'required|string|max:100',
            'fechaInicio' => 'required|date|date_format:Y-m-d',
            'fechaFin' => 'required|date|date_format:Y-m-d|after:fechaInicio',
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ciclo.required' => 'El nombre del ciclo es obligatorio.',
            'ciclo.string' => 'El nombre del ciclo debe ser texto.',
            'ciclo.max' => 'El nombre del ciclo no puede exceder los 100 caracteres.',
            'fechaInicio.required' => 'La fecha de inicio es obligatoria.',
            'fechaInicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fechaInicio.date_format' => 'La fecha de inicio debe tener el formato dd/mm/aaaa.',
            'fechaFin.required' => 'La fecha de fin es obligatoria.',
            'fechaFin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fechaFin.date_format' => 'La fecha de fin debe tener el formato dd/mm/aaaa.',
            'fechaFin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}

