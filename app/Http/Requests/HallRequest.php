<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HallRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        return true; // Cambiar a false si necesitas validación de roles/permisos
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'salon' => 'required|string|max:255',
            'socio' => 'required|numeric|min:0',
            'no_socio' => 'required|numeric|min:0',
        ];
    }
}
