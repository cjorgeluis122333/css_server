<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisteredGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID de la ruta si estamos en un método de actualización (PUT/PATCH)
        $ind = $this->route('ind');

        return [
            'cedula' => [
                'required',
                'string',
                'max:20',
                // Si estamos actualizando, ignoramos el ID actual para la regla unique
                'unique:invitados_registrados,cedula,' . $ind . ',ind'
            ],
            'nombre' => 'required|string|max:150',
            'acc' => 'nullable|integer',
            'last_time' => 'nullable|date',
            'operador' => 'nullable|string|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required' => 'La cédula del invitado es obligatoria.',
            'cedula.unique' => 'Ya existe un invitado registrado con esta cédula.',
            'nombre.required' => 'El nombre del invitado es obligatorio.'
        ];
    }
}
