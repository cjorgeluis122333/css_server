<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManagerBoardsRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'year' => 'required|integer|min:1900|max:2100',
        ];

        // Lista de cargos para validar que la cédula existe en la tabla de managers
        $cargos = [
            'presidente', 'vicepresidente', 'secretario', 'vicesecretario',
            'tesorero', 'vicetesorero', 'bibliotecario', 'actas', 'viceactas',
            'actos', 'deportes', 'vocal1', 'vocal2'
        ];

        foreach ($cargos as $cargo) {
            $rules[$cargo] = 'nullable|exists:0cc_directivos_datos,cedula';
        }

        return $rules;
    }
    public function messages(): array
    {
        return [
            'exists' => 'La cédula del :attribute no corresponde a un directivo registrado.',
            'year.required' => 'El año es obligatorio.'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
