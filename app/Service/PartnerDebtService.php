<?php

namespace App\Service;

use App\Models\Fee;
use App\Models\Partner;
use App\Models\HistoryPay;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PartnerDebtService
{
    /**
     * Extrae todas las deudas pendientes de un socio con sus montos exactos.
     *
     * @param Partner $partner
     * @return Collection
     */
    public function getAccountStatement(Partner $partner): Collection
    {
        // 1. Calcular recargo familiar (16% si hay un Hijo > 30 años)
        $surcharge = $this->calculateFamilySurcharge($partner);

        // 2. Obtener la cuota actual vigente (la última registrada)
        // Asumiendo que el 'ind' más alto o el último registro es el actual
        $currentFee = Fee::orderBy('ind', 'desc')->first();

        // Si por alguna razón no hay cuotas en el sistema, evitamos errores
        if (!$currentFee) {
            return collect();
        }

        $baseCurrentTotal = $currentFee->total * (1 + $surcharge);

        // 3. Agrupar historial de pagos por mes (con la fecha del primer abono)
        $paymentsByMonth = HistoryPay::where('acc', $partner->acc)
            ->selectRaw('mes, SUM(monto) as total_pagado, MIN(fecha) as fecha_primer_pago')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        // 4. Obtener todas las cuotas a evaluar
        $allFees = Fee::orderBy('ind', 'asc')->get();

        $debts = collect();

        foreach ($allFees as $fee) {
            $month = $fee->mes; // Ej: '2026-01'
            $paymentData = $paymentsByMonth->get($month);

            $totalPaid = $paymentData ? (float) $paymentData->total_pagado : 0;

            // 5. Determinar la meta de pago para este mes según las reglas de negocio
            if ($totalPaid == 0) {
                // Escenario 1: No hay pagos. Aplica la cuota ACTUAL vigente.
                $targetAmount = $baseCurrentTotal;
            } else {
                // Escenario 2: Hay pagos. Buscamos la cuota en la fecha del primer abono.
                // Convertimos la fecha (ej. '2026-02-19') al formato de la columna 'mes' (ej. '2026-02')
                $mesDeLaFechaDePago = Carbon::parse($paymentData->fecha_primer_pago)->format('Y-m');

                $cuotaEnEseMomento = Fee::where('mes', $mesDeLaFechaDePago)->first();

                // Si encontramos la cuota de ese momento, la usamos. Si no, usamos la cuota base de ese ciclo.
                $baseHistoricalTotal = $cuotaEnEseMomento ? $cuotaEnEseMomento->total : $fee->total;

                // Le aplicamos el recargo del 16% si corresponde
                $targetAmount = $baseHistoricalTotal * (1 + $surcharge);
            }

            $pendingAmount = $targetAmount - $totalPaid;

            // Si debe algo (la diferencia es mayor a 0), lo agregamos a la colección de deudas
            if ($pendingAmount > 0) {
                $debts->push([
                    'mes' => $month,
                    'cuota_aplicada' => round($targetAmount, 2),
                    'total_pagado' => round($totalPaid, 2),
                    'deuda_pendiente' => round($pendingAmount, 2),
                    'estado' => $totalPaid > 0 ? 'Pago Parcial' : 'Sin Pagar'
                ]);
            }
        }

        return $debts;
    }

    /**
     * Verifica si el socio titular tiene un familiar "Hijo" mayor de 30 años.
     *
     * @param Partner $partner
     * @return float
     */
    private function calculateFamilySurcharge(Partner $partner): float
    {
        // Usamos la relación dependents() que ya está en tu modelo Partner
        $hasAdultChild = $partner->dependents()
            ->where('direccion', 'Hijo')
            ->get()
            ->contains(function ($dependent) {
                // Usamos el accessor getAgeAttribute() de tu modelo
                return $dependent->age > 30;
            });

        return $hasAdultChild ? 0.16 : 0.00;
    }
}
