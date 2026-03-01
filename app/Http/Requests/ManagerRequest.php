<?php

namespace App\Http\Requests;

use App\Models\Manager;
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
        // Intentamos obtener el ID de varias formas comunes en Laravel
        $managerParam = $this->route('manager');

        // Si es un objeto (Route Model Binding), extraemos el acc.
        // Si es un string/int, lo usamos directamente.
        $mangerId = is_object($managerParam) ? $managerParam->acc : $managerParam;

        return [
            'cedula' => [
                'nullable',
                'numeric',
                // Importante: El segundo parámetro de ignore debe ser la COLUMNA clave primaria
                Rule::unique(Manager::class, 'cedula')->ignore($mangerId, 'acc'),
            ],
            'nombre' => 'required|string|max:255',
            'acc' => [
                'required',
                'numeric',
                Rule::unique(Manager::class, 'acc')->ignore($mangerId, 'acc'),
            ],
        ];
    }
    public function authorize(): bool
    {
        return true;
    }
}
