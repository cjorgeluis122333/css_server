<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class StoreInglesPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'mes' => ['required', 'string', 'date_format:Y-m'],
            'plan' => ['nullable', 'string', 'max:255'],
            'monto' => ['required', 'integer', 'min:0'],
            'dolares' => ['required', 'integer', 'min:0'],
            'zelle' => ['required', 'integer', 'min:0'],
            'recibo' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'fecha' => ['nullable', 'integer'],
            'observacion' => ['nullable', 'string', 'max:255'],
            'operador' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.regex' => 'La cédula debe contener solo números.',
            'mes.required' => 'El mes es obligatorio.',
            'mes.date_format' => 'El mes debe tener el formato YYYY-MM (ej: 2024-01).',
            'monto.required' => 'El monto es obligatorio.',
            'monto.integer' => 'El monto debe ser un número entero.',
            'monto.min' => 'El monto no puede ser negativo.',
            'dolares.required' => 'El monto en dólares es obligatorio.',
            'dolares.integer' => 'El monto en dólares debe ser un número entero.',
            'dolares.min' => 'El monto en dólares no puede ser negativo.',
            'zelle.required' => 'El monto de Zelle es obligatorio.',
            'zelle.integer' => 'El monto de Zelle debe ser un número entero.',
            'zelle.min' => 'El monto de Zelle no puede ser negativo.',
            'recibo.required' => 'El número de recibo es obligatorio.',
            'recibo.regex' => 'El número de recibo debe contener solo números.',
            'fecha.integer' => 'La fecha debe ser un timestamp Unix válido.',
        ];
    }
}
