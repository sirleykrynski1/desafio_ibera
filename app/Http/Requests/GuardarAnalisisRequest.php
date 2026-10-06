<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuardarAnalisisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha_muestra' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'laboratorio' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha_muestra.required' => 'La fecha de la muestra es obligatoria.',
            'fecha_muestra.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.',
            'fecha_muestra.before_or_equal' => 'La fecha de la muestra no puede ser futura.',
            'laboratorio.required' => 'El nombre del laboratorio es obligatorio.',
            'laboratorio.string' => 'El nombre del laboratorio debe ser texto.',
            'laboratorio.max' => 'El nombre del laboratorio no puede superar los 255 caracteres.',
        ];
    }
}
