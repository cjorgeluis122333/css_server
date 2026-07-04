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
            'ind' => ['required', 'integer', 'min:1'],
            'cedula' => ['required', 'integer'],
            'mes' => ['required', 'string', 'date_format:Y-m'],
            'plan' => ['nullable', 'string', 'max:255'],
            'monto' => ['required', 'integer', 'min:0'],
            'dolares' => ['required', 'integer', 'min:0'],
            'zelle' => ['required', 'integer', 'min:0'],
            'recibo' => ['required', 'integer', 'min:0'],
            'fecha' => ['nullable', 'integer'],
            'observacion' => ['nullable', 'string', 'max:255'],
            'operador' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ind.required' => 'El índice es obligatorio.',
            'ind.integer' => 'El índice debe ser un número entero.',
            'ind.min' => 'El índice debe ser al menos 1.',
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.integer' => 'La cédula debe ser un número entero.',
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
            'recibo.integer' => 'El número de recibo debe ser un número entero.',
            'recibo.min' => 'El número de recibo no puede ser negativo.',
            'fecha.integer' => 'La fecha debe ser un timestamp Unix válido.',
        ];
    }
}
