<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagerRequest extends FormRequest
{
    public function rules(): array
    {
        /**
         * La instrucción $this->route('directivo') le dice a Laravel: "Oye, busca en la URL el valor que esté en la posición de 'directivo' y dámelo".
         * @example : Route::put('/directivos/{directivo}', [DirectivoController::class, 'update']);
         * @returns:  Si entras a /directivos/5, el parámetro directivo vale 5.
         */
        $mangerId = $this->route('manger');

        return [
            'cedula' => [
                'nullable',
                'numeric',
                Rule::unique('directivos_datos', 'cedula')->ignore($mangerId, 'acc'),
            ],
            'nombre' => 'required|string|max:255',
            'acc' => [
                'required',
                'numeric',
                Rule::unique('directivos_datos', 'acc')->ignore($mangerId, 'acc'),
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
