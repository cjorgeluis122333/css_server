<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class StoreKaratePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'mes' => ['required', 'string', 'max:7'],
            'plan' => ['nullable', 'string', 'max:50'],
            'monto' => ['nullable', 'integer', 'min:0'],
            'dolares' => ['nullable', 'integer', 'min:0'],
            'zelle' => ['nullable', 'integer', 'min:0'],
            'recibo' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'fecha' => ['nullable', 'integer'],
            'observacion' => ['nullable', 'string', 'max:255'],
            'operador' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.regex' => 'La cédula debe contener solo números.',
            'mes.required' => 'El mes es obligatorio.',
            'mes.max' => 'El mes no puede superar 7 caracteres.',
            'monto.integer' => 'El monto debe ser un número entero.',
            'monto.min' => 'El monto no puede ser negativo.',
            'dolares.integer' => 'El monto en dólares debe ser un número entero.',
            'dolares.min' => 'El monto en dólares no puede ser negativo.',
            'zelle.integer' => 'El monto de Zelle debe ser un número entero.',
            'zelle.min' => 'El monto de Zelle no puede ser negativo.',
            'recibo.regex' => 'El número de recibo debe contener solo números.',
            'recibo.min' => 'El número de recibo no puede ser negativo.',
            'fecha.integer' => 'La fecha debe ser un timestamp Unix válido.',
        ];
    }
}
