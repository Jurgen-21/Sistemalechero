<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'   => ['required', 'string', 'min:3', 'max:120'],
            'zona'     => ['required', 'string', 'max:80'],
            'telefono' => ['nullable', 'digits:9'],
            'estado'   => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'Escribe el nombre del proveedor.',
            'nombre.min'        => 'El nombre debe tener al menos 3 caracteres.',
            'zona.required'     => 'Indica la zona del proveedor.',
            'telefono.digits'   => 'El teléfono debe tener 9 dígitos.',
            'estado.required'   => 'Selecciona un estado.',
            'estado.in'         => 'El estado no es válido.',
        ];
    }
}
