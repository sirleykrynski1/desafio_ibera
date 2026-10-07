<?php

namespace App\Http\Requests;

use App\Models\Establecimiento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GuardarEstablecimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $establecimiento = $this->route('establecimiento');
        if ($establecimiento instanceof Establecimiento) {
            Gate::authorize('update', $establecimiento);
        } else {
            Gate::authorize('create', Establecimiento::class);
        }

        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'rubro' => ['required', 'in:hotel,gastronomico,comercio'],
            'cuit' => ['nullable', 'regex:/^[0-9]{11}$/'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'tipo_destino_vuelco' => ['required', 'in:cursos_agua,laguna,conducto_pluvial,absorcion_suelo'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'capacidad_maxima' => ['required', 'integer', 'min:1'],
            'capacidad_biodigestor' => ['required', 'integer', 'min:1'],
        ];
    }
}
