<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class StorePinponPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ind_original' => ['required', 'integer', 'min:1'],
            'anio_origen'  => ['required', 'integer', 'min:2000', 'max:2100'],
            'cedula'       => ['required', 'integer'],
            'mes'          => ['required', 'string', 'max:7', 'date_format:Y-m'],
            'd'            => ['nullable', 'string', 'max:10'],
            'plan'         => ['nullable', 'string', 'max:50'],
            'monto'        => ['required', 'integer', 'min:0'],
            'dolares'      => ['required', 'integer', 'min:0'],
            'zelle'        => ['required', 'integer', 'min:0'],
            'recibo'       => ['required', 'integer', 'min:0'],
            'fecha'        => ['nullable', 'integer'],
            'observacion'  => ['nullable', 'string', 'max:255'],
            'operador'     => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'ind_original.required'  => 'El índice original es obligatorio.',
            'ind_original.integer'   => 'El índice original debe ser un número entero.',
            'ind_original.min'       => 'El índice original debe ser al menos 1.',
            'anio_origen.required'   => 'El año de origen es obligatorio.',
            'anio_origen.integer'    => 'El año de origen debe ser un número entero.',
            'anio_origen.min'        => 'El año de origen debe ser mayor o igual a 2000.',
            'anio_origen.max'        => 'El año de origen debe ser menor o igual a 2100.',
            'cedula.required'        => 'La cédula es obligatoria.',
            'cedula.integer'         => 'La cédula debe ser un número entero.',
            'mes.required'           => 'El mes es obligatorio.',
            'mes.date_format'        => 'El mes debe tener el formato YYYY-MM (ej: 2024-01).',
            'monto.required'         => 'El monto es obligatorio.',
            'monto.integer'          => 'El monto debe ser un número entero.',
            'monto.min'              => 'El monto no puede ser negativo.',
            'dolares.required'       => 'El monto en dólares es obligatorio.',
            'dolares.integer'        => 'El monto en dólares debe ser un número entero.',
            'dolares.min'            => 'El monto en dólares no puede ser negativo.',
            'zelle.required'         => 'El monto de Zelle es obligatorio.',
            'zelle.integer'          => 'El monto de Zelle debe ser un número entero.',
            'zelle.min'              => 'El monto de Zelle no puede ser negativo.',
            'recibo.required'        => 'El número de recibo es obligatorio.',
            'recibo.integer'         => 'El número de recibo debe ser un número entero.',
            'recibo.min'             => 'El número de recibo no puede ser negativo.',
            'fecha.integer'          => 'La fecha debe ser un timestamp Unix válido.',
        ];
    }
}
