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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) return;

            $partner = Partner::where('acc', $this->acc)->first();
            if (!$partner) return;

            $debtService = app(PartnerDebtService::class);
            $ultimoMesAPagar = collect($this->pagos)->pluck('mes')->max();

            // --- CORRECCIÓN AQUÍ ---
            $resultado = $debtService->getAccountStatement($partner, $ultimoMesAPagar);

            // Extraemos 'debts' y lo convertimos en colección para usar keyBy
            $estadoCuenta = collect($resultado['debts'])->keyBy('mes');
            // -----------------------

            foreach ($this->pagos as $index => $pago) {
                $mes = $pago['mes'];
                $montoEnviado = (float) $pago['monto'];

                if (!$estadoCuenta->has($mes)) {
                    $validator->errors()->add(
                        "pagos.{$index}.mes",
                        "El mes {$mes} no está disponible para cobro o no tiene deuda."
                    );
                    continue;
                }

                $infoDeuda = $estadoCuenta->get($mes);
                $limiteEfectivo = (float) ($infoDeuda['efectivo_restante'] ?? 0);

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
