<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class ActivitySemanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'digits:4'],
            'semana' => ['nullable', 'integer', 'min:1', 'max:53'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.integer' => 'El parámetro year debe ser un número entero.',
            'year.digits' => 'El parámetro year debe tener 4 dígitos.',
            'semana.integer' => 'El parámetro semana debe ser un número entero.',
            'semana.min' => 'El parámetro semana debe ser al menos 1.',
            'semana.max' => 'El parámetro semana no puede superar 53.',
        ];
    }
}
