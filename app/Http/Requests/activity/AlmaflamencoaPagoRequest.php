<?php

namespace App\Http\Requests\activity;

use Illuminate\Foundation\Http\FormRequest;

class AlmaflamencoaPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'El parámetro per_page debe ser un número entero.',
            'per_page.min'     => 'El parámetro per_page debe ser al menos 1.',
            'per_page.max'     => 'El parámetro per_page no puede superar 200.',
        ];
    }
}
