<?php

namespace App\Http\Requests\partner;

use App\Enum\PartnerCategory;
use App\Models\partners\Partner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartnerRequest extends FormRequest
{

    public function rules(): array
    {
        // 1. Obtenemos el parámetro de la ruta (acc o partner)
        $accFromRoute = $this->route('partner') ?? $this->route('acc');

        // 2. Si el parámetro es un objeto (por Route Model Binding), extraemos el valor
        // Si es un string/int, lo usamos directamente.
        $accValue = is_object($accFromRoute) ? $accFromRoute->acc : $accFromRoute;

        $partnerId = null;

        if ($accValue) {
            $partnerId = Partner::where('acc', $accValue)
                ->where('categoria', PartnerCategory::TITULAR->value)
                ->value('ind'); // Obtenemos el ID primario 'ind'
        }



        return [
            'acc' => [
                'required',
                'integer',
                Rule::unique('0cc_socios', 'acc')
                    ->where('categoria', PartnerCategory::TITULAR->value)
                    ->ignore($partnerId, 'ind') // Si $partnerId es null, no ignora nada (POST)
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'cedula' => [
                'nullable',
                'max:30',
                // Al actualizar: ignoramos todos los registros del mismo acc (titular + familiares)
                // Al crear: validamos unicidad global (no hay accValue)
                $accValue
                    ? Rule::unique('0cc_socios', 'cedula')->where(fn ($q) => $q->where('acc', '<>', (int) $accValue))
                    : Rule::unique('0cc_socios', 'cedula'),
            ],
            'carnet' => [
                'nullable',
                'string',
//                $accValue
//                    ? Rule::unique('0cc_socios', 'carnet')->where(fn ($q) => $q->where('acc', '<>', (int) $accValue))
//                    : Rule::unique('0cc_socios', 'carnet'),
            ],
            'celular'   => ['nullable', 'string', 'max:30'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'correo'    => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'nacimiento'=> ['required', 'date', 'before:today'],
            'ingreso'   => ['nullable', 'date', 'before_or_equal:today'],
            'ocupacion' => ['nullable', 'string'],
            'cobrador'  => ['nullable', 'int'],
        ];
    }
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'acc.required'       => 'El número de acción (acc) es obligatorio.',
            'acc.integer'        => 'El número de acción debe ser un valor numérico.',
            'acc.unique'         => 'El número de cuenta/acción (acc) ya está en uso.',

            'nombre.required'    => 'El nombre del directivo es obligatorio.',
            'nombre.max'         => 'El nombre no puede superar los 255 caracteres.',

            'cedula.unique'      => 'Esta cédula ya se encuentra registrada.',
            'cedula.max'         => 'La cédula no puede superar los 30 caracteres.',

            'carnet.unique'      => 'Este número de carnet ya está registrado en otro socio.',

            'correo.email'       => 'El correo electrónico no tiene un formato válido.',

            'nacimiento.required'=> 'La fecha de nacimiento es obligatoria.',
            'nacimiento.date'    => 'La fecha de nacimiento no es válida.',
            'nacimiento.before'  => 'La fecha de nacimiento debe ser anterior a hoy.',

            'ingreso.date'       => 'La fecha de ingreso no es válida.',
            'ingreso.before_or_equal' => 'La fecha de ingreso no puede ser posterior a hoy.',

            'cobrador.int'       => 'El cobrador debe ser un valor numérico.',
        ];
    }
}
