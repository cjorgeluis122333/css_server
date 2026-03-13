<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HallControlRequest extends FormRequest
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
            'fecha'  => 'nullable|date',
            'salon'  => 'required|string|max:30',
            'acc'    => 'nullable|integer',
            'nombre' => 'nullable|string|max:50',
            'abono'  => 'nullable|numeric|min:0',
            'pago'   => 'nullable|numeric|min:0',
            'pases'  => 'nullable|integer',
            'hora'   => 'nullable|string|max:50',
        ];
    }
}
