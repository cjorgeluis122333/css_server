<?php

namespace App\Service;

use App\Models\Fee;
use App\Models\Partner;
use App\Models\HistoryPay;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class PartnerDebtService
{
    /** * Extrae todas las deudas pendientes de un socio con sus montos exactos,
     * limitando desde su fecha de ingreso hasta el mes actual.
     * * @param Partner $partner
     * @return Collection
     * @throws Exception
     */
    public function getAccountStatement(Partner $partner): Collection
    {
        // 1. Calcular recargo familiar (Multiplicador directo: 1.25 o 1.00)
        $surchargeMultiplier = 1 + $this->calculateFamilySurcharge($partner);

        // 2. Cargar TODAS las cuotas en memoria como una tabla hash (Lookup Table)
        // Esto evita hacer consultas SQL dentro del bucle (Solución al N+1)
        $allFeesLookup = Fee::all()->keyBy('mes');

        // 1. Definimos la clave del mes que queremos buscar (ej: '2026-03')
        $currentMonthKey = now()->format('Y-m');

        // 2. Intentamos obtener la cuota exacta para este mes
        $currentFee = $allFeesLookup->get($currentMonthKey);

       // 3. Si no existe una cuota específica para este mes, buscamos la más reciente que NO sea futura
        if (!$currentFee) {
            $currentFee = $allFeesLookup
                ->filter(fn($fee, $key) => $key <= $currentMonthKey)
                ->last();
        }

        // Opcional: Manejo en caso de que la colección esté vacía
        if (!$currentFee) {
            // Aquí podrías lanzar una excepción o asignar un valor por defecto
            throw new Exception("No se encontró una cuota configurada para el periodo: $currentMonthKey");
        }
        $baseCurrentTotal = $currentFee->total * $surchargeMultiplier;

        // 3. Agrupar historial de pagos del socio
        $paymentsByMonth = HistoryPay::where('acc', $partner->acc)
            ->selectRaw('mes, SUM(monto) as total_pagado, MIN(fecha) as fecha_primer_pago')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        // 4. Obtener la fecha validada desde el modelo
        $startMonth = $partner->fecha_ingreso_validada;

        $debts = collect();

        // 5. Filtrar cuotas
        $feesToEvaluate = $allFeesLookup->filter(function ($fee, $mes) use ($startMonth, $currentMonthKey) {
            // Si $startMonth es null, la primera condición siempre es true (lista todo el historial)
            $isAfterOrEqualIngreso = !$startMonth || $mes >= $startMonth;

            // Mantenemos el límite superior para no cobrar cuotas futuras
            return $isAfterOrEqualIngreso && $mes <= $currentMonthKey;
        });

        foreach ($feesToEvaluate as $month => $fee) {

            $paymentData = $paymentsByMonth->get($month);
            $totalPaid = $paymentData ? (float)$paymentData->total_pagado : 0;

            // 6. Determinar la meta de pago según tu lógica (Solución Problema 1)
            if ($totalPaid == 0) {
                // Escenario 1: No hay pagos. Se le cobra a la tarifa actual.
                $targetAmount = $baseCurrentTotal;
            } else {
                // Escenario 2: Hay un pago parcial. Buscamos la tarifa que existía
                // en el momento en que hizo ese primer pago.
                $mesDeLaFechaDePago = Carbon::parse($paymentData->fecha_primer_pago)->format('Y-m');

                // Buscamos en nuestra colección en memoria (rápido, sin BD)
                $cuotaEnEseMomento = $allFeesLookup->get($mesDeLaFechaDePago);

                $baseHistoricalTotal = $cuotaEnEseMomento ? $cuotaEnEseMomento->total : $fee->total;
                $targetAmount = $baseHistoricalTotal * $surchargeMultiplier;
            }

            $pendingAmount = $targetAmount - $totalPaid;

            // 7. Si debe algo, lo registramos
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

        return $debts->values(); // Resetear los índices de la colección
    }

    /** * Verifica si el socio titular tiene un familiar "Hijo" mayor de 30 años.
     * @param Partner $partner
     * @return float
     */
    private function calculateFamilySurcharge(Partner $partner): float
    {
        // Eager loading ya debería estar manejado fuera, pero buscamos en la relación
        $hasAdultChild = $partner->dependents->contains(function ($dependent) {
            // Usar strtolower evita bugs si escriben 'hijo' o 'HIJO' en base de datos
            return strtolower($dependent->direccion) === 'hijo'
                && $dependent->age !== null
                && $dependent->age > 30;
        });

        return $hasAdultChild ? 0.25 : 0.00;
    }
}
