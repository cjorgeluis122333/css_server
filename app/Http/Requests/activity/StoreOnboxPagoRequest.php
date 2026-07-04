<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class StoreOnboxPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula'      => ['required', 'integer'],
            'mes'         => ['required', 'string', 'max:7', 'date_format:Y-m'],
            'd'           => ['nullable', 'string', 'max:10'],
            'plan'        => ['nullable', 'string', 'max:50'],
            'monto'       => ['required', 'numeric', 'min:0'],
            'dolares'     => ['required', 'numeric', 'min:0'],
            'zelle'       => ['required', 'numeric', 'min:0'],
            'recibo'      => ['required', 'integer', 'min:0'],
            'fecha'       => ['nullable', 'integer'],
            'observacion' => ['nullable', 'string'],
            'operador'    => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required'      => 'La cédula es obligatoria.',
            'cedula.integer'       => 'La cédula debe ser un número entero.',
            'mes.required'         => 'El mes es obligatorio.',
            'mes.date_format'      => 'El mes debe tener el formato YYYY-MM (ej: 2024-01).',
            'monto.required'       => 'El monto es obligatorio.',
            'monto.numeric'        => 'El monto debe ser un valor numérico.',
            'monto.min'            => 'El monto no puede ser negativo.',
            'dolares.required'     => 'El monto en dólares es obligatorio.',
            'dolares.numeric'      => 'El monto en dólares debe ser un valor numérico.',
            'dolares.min'          => 'El monto en dólares no puede ser negativo.',
            'zelle.required'       => 'El monto de Zelle es obligatorio.',
            'zelle.numeric'        => 'El monto de Zelle debe ser un valor numérico.',
            'zelle.min'            => 'El monto de Zelle no puede ser negativo.',
            'recibo.required'      => 'El número de recibo es obligatorio.',
            'recibo.integer'       => 'El número de recibo debe ser un número entero.',
            'recibo.min'           => 'El número de recibo no puede ser negativo.',
            'fecha.integer'        => 'La fecha debe ser un timestamp Unix válido.',
        ];
    }
}
