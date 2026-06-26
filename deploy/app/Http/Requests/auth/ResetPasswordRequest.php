<?php

namespace App\Http\Requests\auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'acc'      => 'required|integer',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'acc.required'           => 'El número de acción es obligatorio.',
            'acc.integer'            => 'El número de acción debe ser un número entero.',
            'password.required'      => 'La nueva contraseña es obligatoria.',
            'password.min'           => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'     => 'La confirmación de la contraseña no coincide.',
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
