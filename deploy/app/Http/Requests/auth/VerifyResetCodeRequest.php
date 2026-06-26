<?php

namespace App\Http\Requests\auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyResetCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'acc'  => 'required|integer',
            'code' => 'required|string|size:6',
        ];
    }

    public function messages(): array
    {
        return [
            'acc.required'  => 'El número de acción es obligatorio.',
            'acc.integer'   => 'El número de acción debe ser un número entero.',
            'code.required' => 'El código de verificación es obligatorio.',
            'code.string'   => 'El código de verificación debe ser una cadena de texto.',
            'code.size'     => 'El código de verificación debe tener exactamente 6 dígitos.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Errores de validación',
            'errors'  => $validator->errors(),
        ], 422));
    }

    public function authorize(): bool
    {
        return true;
    }
}
