<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Asumiendo que la autorización se maneja por middleware (ej. Sanctum)
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula'   => 'required|integer',
            'nombre'   => 'required|string|max:255',
            'fecha'    => 'required|date',
            'acc'      => 'required|integer',
            'fuente'   => 'nullable|string|max:255',
            'operador' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required' => 'La cédula del invitado es obligatoria.',
            'fecha.required'  => 'La fecha de la invitación es obligatoria.',
            'acc.required'    => 'La acción del socio es obligatoria.',
        ];
    }
}
