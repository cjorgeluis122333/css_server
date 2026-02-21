<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enum\PartnerCategory;
use Illuminate\Validation\Rule;
class FamilyRequest extends FormRequest
{
    public function rules(): array
    {
        // Obtenemos el ID del familiar desde la ruta (ej: PUT /api/families/{id})
        // Asegúrate de usar el nombre correcto del parámetro que definiste en api.php
        $familyId = $this->route('family') ?? $this->route('id');
        return [
            'acc' => [
                // Es obligatorio al crear. Al actualizar (PUT), 'sometimes' permite omitirlo
                // para que no cambien a un familiar de titular por accidente.
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'integer',
                // Regla clave: La ACC debe existir en la base de datos Y pertenecer a un TITULAR
                Rule::exists('0cc_socios', 'acc')->where(function ($query) {
                    $query->where('categoria', PartnerCategory::TITULAR->value);
                })
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'cedula' => [
                'nullable',
                'max:30',
                // Debe ser única, pero ignoramos la del familiar que estamos editando
                Rule::unique('0cc_socios', 'cedula')->ignore($familyId, 'ind')
            ],
            'carnet' => [
                'nullable',
                'string',
                Rule::unique('0cc_socios', 'carnet')->ignore($familyId, 'ind')
            ],
            'celular'    => ['nullable', 'string', 'max:30'],
            'nacimiento' => ['nullable', 'date', 'before:today'],
            'direccion'  => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
