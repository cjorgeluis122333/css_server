<?php

namespace App\Http\Requests;

use App\Enum\PartnerCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartnerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'acc' => [
                'required',
                'integer',
                // Regla de Negocio: No puede haber dos TITULARES con la misma acción.
                // (Los familiares sí comparten acción, pero aquí estamos creando Titulares).
                Rule::unique('0cc_socios', 'acc')->where(function ($query) {
                    return $query->where('categoria', PartnerCategory::TITULAR->value);
                })
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:25', 'unique:0cc_socios,cedula'],
            'carnet' => ['required', 'string', 'max:11', 'unique:0cc_socios,carnet'],

            'celular' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string'],

            'nacimiento' => ['nullable', 'date', 'before:today'],
            'ingreso' => ['required', 'date', 'before:today'],
            'ocupacion' => ['required', 'string'],
            'cobrador' => ['required', 'int'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

}
