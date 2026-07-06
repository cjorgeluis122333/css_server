<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class ActivityMesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'digits:4'],
            'mes' => ['nullable', 'integer', 'min:1', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.integer' => 'El parámetro year debe ser un número entero.',
            'year.digits' => 'El parámetro year debe tener 4 dígitos.',
            'mes.integer' => 'El parámetro mes debe ser un número entero.',
            'mes.min' => 'El parámetro mes debe ser al menos 1.',
            'mes.max' => 'El parámetro mes no puede superar 12.',
        ];
    }
}
