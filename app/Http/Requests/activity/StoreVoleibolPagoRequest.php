<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoleibolPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula'      => ['required', 'integer'],
            'mes'         => ['required', 'string', 'max:10'],
            'plan'        => ['nullable', 'string', 'max:50'],
            'monto'       => ['required', 'integer', 'min:0'],
            'dolares'     => ['required', 'integer', 'min:0'],
            'zelle'       => ['required', 'integer', 'min:0'],
            'recibo'      => ['required', 'integer', 'min:0'],
            'fecha'       => ['required', 'integer'],
            'observacion' => ['nullable', 'string', 'max:255'],
            'operador'    => ['nullable', 'string', 'max:50'],
            'ano_origen'  => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required'     => 'La cédula es obligatoria.',
            'cedula.integer'      => 'La cédula debe ser un número entero.',
            'mes.required'        => 'El mes es obligatorio.',
            'mes.max'             => 'El mes no puede superar 10 caracteres.',
            'monto.required'      => 'El monto es obligatorio.',
            'monto.integer'       => 'El monto debe ser un número entero.',
            'monto.min'           => 'El monto no puede ser negativo.',
            'dolares.required'    => 'El monto en dólares es obligatorio.',
            'dolares.integer'     => 'El monto en dólares debe ser un número entero.',
            'dolares.min'         => 'El monto en dólares no puede ser negativo.',
            'zelle.required'      => 'El monto de Zelle es obligatorio.',
            'zelle.integer'       => 'El monto de Zelle debe ser un número entero.',
            'zelle.min'           => 'El monto de Zelle no puede ser negativo.',
            'recibo.required'     => 'El número de recibo es obligatorio.',
            'recibo.integer'      => 'El número de recibo debe ser un número entero.',
            'recibo.min'          => 'El número de recibo no puede ser negativo.',
            'fecha.required'      => 'La fecha es obligatoria.',
            'fecha.integer'       => 'La fecha debe ser un timestamp Unix válido.',
            'ano_origen.required' => 'El año de origen es obligatorio.',
            'ano_origen.integer'  => 'El año de origen debe ser un número entero.',
            'ano_origen.min'      => 'El año de origen debe ser mayor o igual a 2000.',
            'ano_origen.max'      => 'El año de origen debe ser menor o igual a 2100.',
        ];
    }
}
