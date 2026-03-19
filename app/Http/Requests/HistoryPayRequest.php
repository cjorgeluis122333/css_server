<?php

namespace App\Http\Requests;

use App\Models\Partner;
use App\Service\PartnerDebtService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class HistoryPayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Identificación del socio (acc)
            'acc'           => 'required|integer|exists:0cc_socios,acc',

            // Lista de pagos (mes y monto efectivo)
            'pagos'           => 'required|array|min:1',
            'pagos.*.mes'     => 'required|string|max:20',
            'pagos.*.monto'   => 'required|numeric|min:0.01',

            // Metadatos de la operación (basados en tu tabla)
            'oper'          => 'nullable|string|max:50',
            'resibo'        => 'nullable|string|max:50',
            'control'       => 'nullable|string|max:50',
            'factura'       => 'nullable|string|max:50',
            'descript'      => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:100',
            'seniat'        => 'nullable|string|max:100',
            'operador'      => 'nullable|string',
            'time'          => 'nullable|string|max:50',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }


    /**
     * Validación personalizada para reglas de negocio (Deuda y límites).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) return;

            // 1. Localizamos al socio
            $partner = Partner::where('acc', $this->acc)->first();
            if (!$partner) return;

            // 2. Obtenemos el servicio de deudas (Inyección manual para el request)
            $debtService = app(PartnerDebtService::class);

            // Extraemos los meses que vienen en el request para que el servicio los analice
            $mesesSolicitados = collect($this->pagos)->pluck('mes')->toArray();
            $estadoCuenta = $debtService->getAccountStatement($partner, $mesesSolicitados)->keyBy('mes');

            // 3. Validamos cada pago individualmente
            foreach ($this->pagos as $index => $pago) {
                $mes = $pago['mes'];
                $montoEnviado = (float) $pago['monto'];

                // Regla A: El mes debe existir en el estado de cuenta (debe tener deuda)
                if (!$estadoCuenta->has($mes)) {
                    $validator->errors()->add(
                        "pagos.{$index}.mes",
                        "El mes {$mes} ya se encuentra pagado al completo o no tiene deuda pendiente."
                    );
                    continue;
                }

                $infoDeuda = $estadoCuenta->get($mes);
                $limiteEfectivo = (float) $infoDeuda['efectivo_restante'];

                // Regla B: El monto no puede superar lo requerido (Margen de 0.01 por decimales)
                if (round($montoEnviado, 2) > round($limiteEfectivo, 2)) {
                    $validator->errors()->add(
                        "pagos.{$index}.monto",
                        "El monto {$montoEnviado} para el mes {$mes} supera la deuda actual de {$limiteEfectivo}."
                    );
                }
            }
        });
    }

    /**
     * Mensajes de error personalizados (opcional).
     */
    public function messages(): array
    {
        return [
            'acc.exists' => 'La cuenta de socio (acc) no existe en el sistema.',
            'pagos.*.monto.min' => 'El monto del pago debe ser mayor a cero.',
        ];
    }

}
