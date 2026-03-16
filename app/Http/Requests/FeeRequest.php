<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeeRequest extends FormRequest
{
    public function rules(): array
    {
        // Validamos que el mes tenga formato YYYY-MM (ej: 2018-01)
        $mesRegex = '/^\d{4}-(0[1-9]|1[0-2])$/';

        return [
            'mes' => ['required', 'string', 'regex:' . $mesRegex],
            'cuota' => 'required|numeric|min:0',
            'impuesto' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'mes.regex' => 'El formato del mes debe ser YYYY-MM (ejemplo: 2018-01).',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
