<?php

namespace App\Http\Requests;

use App\Enum\PartnerCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Partner;

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
                // Importante: Solo aplicamos ignore si realmente encontramos un ID
                Rule::unique('0cc_socios', 'cedula')->ignore($partnerId, 'ind')
            ],
            'carnet' => [
                'nullable',
                'string',
                Rule::unique('0cc_socios', 'carnet')->ignore($partnerId, 'ind')
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

}
