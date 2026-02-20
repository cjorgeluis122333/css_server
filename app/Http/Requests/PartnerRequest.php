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
        // IMPORTANTE: Asegúrate de que el nombre del parámetro aquí ('partner' o 'acc')
        // coincida con lo que pusiste en tu archivo routes/api.php.
        // Si tu ruta es Route::put('/partners/{acc}', ...), usa 'acc' abajo.
        $routeAcc = $this->route('partner') ?? $this->route('acc');

        // Buscamos el ID interno (ind) basado en la ACC de la URL
        $partnerId = null;

        if ($routeAcc) {
            $partnerId = Partner::where('acc', $routeAcc)
                ->where('categoria', PartnerCategory::TITULAR)
                ->value('ind'); // Obtenemos el ID primario (ej: 913)
        }

        return [
            'acc' => [
                'required',
                'integer',
                // Regla: Único en la tabla, ignorando el ID real (ind) que encontramos arriba
                Rule::unique('0cc_socios', 'acc')
                    ->where('categoria', PartnerCategory::TITULAR->value)
                    ->ignore($partnerId, 'ind')
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'cedula' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('0cc_socios', 'cedula')->ignore($partnerId, 'ind')
            ],
            'carnet' => [
                'nullable',
                'string',
                Rule::unique('0cc_socios', 'carnet')->ignore($partnerId, 'ind')
            ],
            // ... resto de campos
            'celular'   => ['nullable', 'string', 'max:20'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'correo'    => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'nacimiento'=> ['nullable', 'date', 'before:today'],
            'ingreso'   => ['required', 'date', 'before:today'],
            'ocupacion' => ['required', 'string'],
            'cobrador'  => ['required', 'int'],
        ];
    }
    public function authorize(): bool
    {
        return true;
    }

}
