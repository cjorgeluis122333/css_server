<?php

namespace App\Http\Requests\partner;

use App\Enum\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class HallControlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Para PUT/PATCH el salon ya existe en el registro — no debe requerirse ni modificarse.
        // Para POST es obligatorio identificar a qué salón corresponde el nuevo registro.
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'fecha'  => 'nullable|date',
            'salon'  => $isUpdate ? 'sometimes|string|max:30' : 'required|string|max:30',
            'acc'    => 'nullable|integer',
            'nombre' => 'nullable|string|max:50',
            'abono'  => 'nullable|numeric|min:0',
            'pago'   => 'nullable|numeric|min:0',
            'pases'  => 'nullable|integer',
            'hora'   => 'nullable|string|max:50',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $acc = $this->input('acc');
            $nombre = $this->input('nombre');

            // --- Reglas de autorización por rol ---
            if ($user && $user->hasRole(UserRole::PARTNER, UserRole::HONORARY)) {
                // PARTNER/HONORARY no pueden procesar pagos (campo pago o abono)
                if ($this->filled('pago') && $this->input('pago') > 0) {
                    $validator->errors()->add('pago', 'Solo administración puede procesar pagos de salones.');
                }
                if ($this->filled('abono') && $this->input('abono') > 0) {
                    $validator->errors()->add('abono', 'Solo administración puede procesar abonos de salones.');
                }

                // PARTNER/HONORARY solo pueden operar con su propia acción
                if ($this->filled('acc') && (int) $acc !== $user->acc) {
                    $validator->errors()->add('acc', 'Solo puedes realizar operaciones con tu propia acción.');
                }
            }

            // --- Reglas de validación de datos existentes ---
            $modificandoExtras = $this->filled('abono') || $this->filled('pago') || $this->filled('pases') || $this->filled('hora');

            $accVacioO0 = is_null($acc) || $acc == 0;
            $nombreVacio = empty($nombre);

            if ($modificandoExtras) {
                if (is_null($acc)) {
                    $validator->errors()->add('acc', 'La acción es obligatoria al modificar pagos u otros detalles.');
                }
                if ($nombreVacio) {
                    $validator->errors()->add('nombre', 'El nombre es obligatorio al modificar pagos u otros detalles.');
                }
            } else {
                if (!$accVacioO0 && $nombreVacio) {
                    $validator->errors()->add('nombre', 'El nombre es requerido cuando la acción es distinta de 0.');
                }

                if (!$nombreVacio && $accVacioO0) {
                    $validator->errors()->add('acc', 'La acción es requerida y debe ser distinta de 0 si se ingresa un nombre.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'salon.required' => 'El nombre del salón es obligatorio.',
            'salon.string' => 'El salón debe ser texto.',
            'pago.numeric' => 'El pago debe ser un valor numérico.',
            'abono.numeric' => 'El abono debe ser un valor numérico.',
        ];
    }
}
