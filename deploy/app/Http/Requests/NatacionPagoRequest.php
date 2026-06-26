<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NatacionPagoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        // Cambiar a true para permitir el acceso, o manejar aquí la lógica de permisos
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplicarán a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cedula'      => ['nullable', 'integer', 'min:0'],
            'anio'        => ['required', 'integer', 'digits:4'],
            'mes'         => ['nullable', 'string', 'max:255'],
            'plan'        => ['nullable', 'string', 'max:255'],
            'monto'       => ['required', 'integer'],
            'dolares'     => ['required', 'integer'],
            'zelle'       => ['required', 'integer'],
            'recibo'      => ['required', 'integer'],
            'fecha'       => ['required', 'integer'], // Si es formato AAAAMMDD o timestamp, puedes meter reglas 'min' adicionales
            'observacion' => ['nullable', 'string', 'max:255'],
            'operador'    => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Mensajes de error personalizados (Opcional).
     */
    public function messages(): array
    {
        return [
            'anio.required'  => 'El año es obligatorio para identificar el origen de los datos.',
            'anio.digits'    => 'El año debe tener exactamente 4 dígitos.',
            'monto.required' => 'El monto del pago es requerido.',
        ];
    }
}
