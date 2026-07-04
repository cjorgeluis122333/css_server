<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNatacionClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula'        => ['required', 'integer', Rule::unique('0cc_natacion_clientes', 'cedula')],
            'nombre'        => ['required', 'string', 'max:255'],
            'socio'         => ['required', 'string', Rule::in(['Socio', 'No Socio'])],
            'nacimiento'    => ['required', 'date'],
            'sexo'          => ['required', 'string', 'max:50'],
            'padres'        => ['nullable', 'string', 'max:255'],
            'repre_cedula1' => ['nullable', 'string', 'max:50'],
            'repre_nombre1' => ['nullable', 'string', 'max:255'],
            'repre_cedula2' => ['nullable', 'string', 'max:50'],
            'repre_nombre2' => ['nullable', 'string', 'max:255'],
            'repre_cedula3' => ['nullable', 'string', 'max:50'],
            'repre_nombre3' => ['nullable', 'string', 'max:255'],
            'operador'      => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.integer'  => 'La cédula debe ser un número entero.',
            'cedula.unique'   => 'Esta cédula ya está registrada en natación.',
            'nombre.required' => 'El nombre es obligatorio.',
            'socio.required'  => 'El campo socio es obligatorio.',
            'socio.in'        => 'El valor de socio debe ser "Socio" o "No Socio".',
            'nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'nacimiento.date'     => 'La fecha de nacimiento no tiene un formato válido.',
            'sexo.required'   => 'El sexo es obligatorio.',
        ];
    }
}
