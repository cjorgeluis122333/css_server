<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HallControlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Reglas base (tipos de datos)
        return [
            'acc'    => 'nullable|integer',
            'nombre' => 'nullable|string|max:50',
            'abono'  => 'nullable|numeric|min:0',
            'pago'   => 'nullable|numeric|min:0',
            'pases'  => 'nullable|integer',
            'hora'   => 'nullable|string|max:50',
        ];
    }

    /**
     * Aquí aplicamos tu lógica de negocio específica.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $acc = $this->input('acc');
            $nombre = $this->input('nombre');
            
            // Verificamos si se está modificando algún otro campo (pago, abono, pases, hora)
            $modificandoExtras = $this->filled('abono') || $this->filled('pago') || $this->filled('pases') || $this->filled('hora');

            // Condiciones auxiliares
            $accVacioO0 = is_null($acc) || $acc == 0;
            $nombreVacio = empty($nombre);

            if ($modificandoExtras) {
                // "Si se modifica cualquier otro de los campos, la accion y el nombre estaran requeridos"
                if (is_null($acc)) {
                    $validator->errors()->add('acc', 'La acción es obligatoria al modificar pagos u otros detalles.');
                }
                if ($nombreVacio) {
                    $validator->errors()->add('nombre', 'El nombre es obligatorio al modificar pagos u otros detalles.');
                }
            } else {
                // "Si ambos estan vacios deja que lo modifique" -> Si $accVacioO0 y $nombreVacio son true, no entra en los IFs y pasa limpio.

                // "Si se pone una accion distinta de 0 el nombre sera requerido"
                if (!$accVacioO0 && $nombreVacio) {
                    $validator->errors()->add('nombre', 'El nombre es requerido cuando la acción es distinta de 0.');
                }

                // "Si se pone un nombre, la accion sera requerida"
                if (!$nombreVacio && $accVacioO0) {
                    $validator->errors()->add('acc', 'La acción es requerida y debe ser distinta de 0 si se ingresa un nombre.');
                }
            }
        });
    }
}