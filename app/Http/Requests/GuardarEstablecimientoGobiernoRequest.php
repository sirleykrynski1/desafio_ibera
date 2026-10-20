<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class GuardarEstablecimientoGobiernoRequest extends GuardarEstablecimientoRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->es('admin_gobierno') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('rol', 'propietario')],
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Seleccioná el propietario del establecimiento.',
            'user_id.exists' => 'Seleccioná una cuenta con rol de propietario.',
            'latitud.required' => 'Ingresá la latitud real del establecimiento.',
            'longitud.required' => 'Ingresá la longitud real del establecimiento.',
        ];
    }
}
