<?php

namespace App\Http\Requests;

use App\Models\Establecimiento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GuardarAnalisisRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }
        if (filter_var($this->input('establecimiento_id'), FILTER_VALIDATE_INT) === false) {
            return true;
        }
        Gate::authorize('update', $this->establecimientoAutorizado());

        return true;
    }

    public function establecimientoAutorizado(): Establecimiento
    {
        return Establecimiento::findOrFail($this->integer('establecimiento_id'));
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'establecimiento_id' => ['required', 'integer'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'extensions:pdf', 'max:10240'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'establecimiento_id.required' => 'Seleccioná un establecimiento.',
            'establecimiento_id.integer' => 'El establecimiento no es válido.',
            'pdf.required' => 'Adjuntá el informe PDF.', 'pdf.file' => 'El informe debe ser un archivo.',
            'pdf.mimes' => 'El informe debe ser un PDF.', 'pdf.extensions' => 'El archivo debe tener extensión .pdf.',
            'pdf.max' => 'El PDF no puede superar los 10 MB.',
        ];
    }
}
