<?php

namespace App\Http\Requests\auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DirectPasswordValidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'acc' => 'required|integer',
            'cedula' => 'required|integer',
            'correo' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'acc.required' => 'El número de acción es obligatorio.',
            'acc.integer' => 'El número de acción debe ser un número entero.',
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.integer' => 'La cédula debe ser un número entero.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico no tiene un formato válido.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Errores de validación',
            'errors' => $validator->errors(),
        ], 422));
    }

    public function authorize(): bool
    {
        return true;
    }
}
