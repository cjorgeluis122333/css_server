<?php

namespace App\Service;

use App\Models\Fee;
use App\Models\Partner;
use App\Models\HistoryPay;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PartnerDebtService
{
    public function getAccountStatement(Partner $partner): Collection
    {
        // 1. Calcular recargo familiar (16%)
        $surcharge = $this->calculateFamilySurcharge($partner);

        // 2. Obtener cuota actual
        $currentFee = Fee::orderBy('ind', 'desc')->first();
        $baseCurrentTotal = $currentFee->total * (1 + $surcharge);

        // 3. Agrupar historial de pagos por mes para esta cuenta
        // Retorna un array asociativo: ['01-2023' => 20.50, '02-2023' => 45.00]
       // Agrupamos los pagos por el mes al que van dirigidos
        $paymentsByMonth = HistoryPay::where('acc', $partner->acc)
            ->selectRaw('mes, SUM(monto) as total_pagado, MIN(fecha) as fecha_primer_pago')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');
        // 4. Obtener todas las cuotas (Asumiendo que quieres evaluar todas o desde su ingreso)
        $allFees = Fee::orderBy('ind', 'asc')->get();

        $debts = collect();

        foreach ($allFees as $fee) {
            $month = $fee->mes;
            $paymentData = $paymentsByMonth->get($month);
            $totalPaid = $paymentData ? (float)$paymentData->total_pagado : 0;

            // Determinar cuánto debería costar este mes
            if ($totalPaid == 0) {
                // Si no hay pagos, aplica la regla de "cobrar la cuota actual"
                $targetAmount = $baseCurrentTotal;
            } else {
                // Si hay pagos (parciales o totales)
                // Usamos la cuota fijada (Opción B) o caemos a la cuota histórica
                $historicTotal = $fee->total * (1 + $surcharge);
                $targetAmount = $paymentData->cuota_fijada ? (float)$paymentData->cuota_fijada : $historicTotal;
            }

            $pendingAmount = $targetAmount - $totalPaid;

            // Si debe algo, lo agregamos a la lista de deudas
            if ($pendingAmount > 0) {
                $debts->push([
                    'mes' => $month,
                    'cuota_aplicada' => $targetAmount,
                    'total_pagado' => $totalPaid,
                    'deuda_pendiente' => round($pendingAmount, 2),
                    'estado' => $totalPaid > 0 ? 'Pago Parcial' : 'Sin Pagar'
                ]);
            }
        }

        return $debts;
    }

    private function calculateFamilySurcharge(Partner $partner): float
    {
        $hasAdultChild = Partner::where('acc', $partner->acc)
            ->onlyDependents()
            ->where('direccion', 'Hijo') // O el string exacto que uses
            ->get()
            ->contains(function ($dependent) {
                return $dependent->age > 30;
            });

        return $hasAdultChild ? 0.16 : 0.00;
    }
}
