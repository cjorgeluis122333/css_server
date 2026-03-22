<?php

namespace App\Service;

use App\Models\Fee;
use App\Models\Partner;
use App\Models\HistoryPay;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Collection;

class PartnerDebtService
{
    /**
     * @param Partner $partner
     * @param array $paymentsList Ejemplo: [['mes' => '2026-03', 'monto' => 36.656]]
     * @param array $paymentMetadata Datos extra (oper, operador, etc.)
     * @throws Exception
     */
    public function processPayments(Partner $partner, array $paymentsList, array $paymentMetadata = [])
    {
        // Ya no enviamos los meses solicitados porque no procesaremos adelantados aquí
        $statement = $this->getAccountStatement($partner)->keyBy('mes');

        DB::beginTransaction();
        try {
            foreach ($paymentsList as $pago) {
                $mes = $pago['mes'];
                $montoEfectivoEnviado = (float)$pago['monto'];

                if ($montoEfectivoEnviado <= 0) continue;

                if (!$statement->has($mes)) {
                    throw new Exception("El mes {$mes} no presenta deudas, es un mes futuro o no es válido para este socio.");
                }

                $deudaData = $statement->get($mes);

                // REGLA: El pago no puede superar la deuda del mes
                if (round($montoEfectivoEnviado, 2) > round($deudaData['efectivo_restante'], 2)) {
                    throw new Exception("El monto enviado ({$montoEfectivoEnviado}) para el mes {$mes} supera la deuda real que es de {$deudaData['efectivo_restante']}.");
                }

                // MAGIA: Escalamos el dinero en efectivo al valor nominal de la base de datos
                $montoAInsertar = $montoEfectivoEnviado * $deudaData['factor_conversion'];

                HistoryPay::create([
                    'acc' => $partner->acc,
                    'mes' => $mes,
                    'fecha' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'monto' => round($montoAInsertar, 2),
                    'oper' => $paymentMetadata['oper'] ?? null,
                    'resibo' => $paymentMetadata['resibo'] ?? null,
                    'control' => $paymentMetadata['control'] ?? null,
                    'factura' => $paymentMetadata['factura'] ?? null,
                    'descript' => $paymentMetadata['descript'] ?? null,
                    'observaciones' => $paymentMetadata['observaciones'] ?? null,
                    'seniat' => $paymentMetadata['seniat'] ?? null,
                    'operador' => $paymentMetadata['operador'] ?? null,
                ]);
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Extrae todas las deudas pendientes de un socio con sus montos exactos y el impuesto,
     * limitando desde su fecha de ingreso (o 2019-01) hasta el mes actual.
     *
     * @param Partner $partner
     * @return Collection
     * @throws Exception
     */
    public function getAccountStatement(Partner $partner): Collection
    {
        // 1. Calcular recargo familiar (Retorna 0.00 o 0.25)
        $surchargeMultiplier = $this->calculateFamilySurcharge($partner);

        // 2. Cargar TODAS las cuotas en memoria y ordenarlas por mes
        $allFeesLookup = Fee::all()->keyBy('mes')->sortKeys();

        $currentMonthKey = now()->format('Y-m');

        // Buscar la cuota vigente actual
        $currentFee = $allFeesLookup->filter(fn($fee, $key) => $key <= $currentMonthKey)->last();

        if (!$currentFee) {
            throw new Exception("No se encontró una cuota base configurada en el sistema.");
        }

        // 3. Agrupar historial de pagos del socio
        $paymentsByMonth = HistoryPay::where('acc', $partner->acc)
            ->selectRaw('mes, SUM(monto) as total_pagado, MIN(fecha) as fecha_primer_pago')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        // 4. Determinar el rango de meses a evaluar (Regla del 2019-01)
        $fechaIngreso = $partner->fecha_ingreso ?? $partner->fecha_ingreso_validada;
        $fechaLimite = Carbon::create(2019, 1, 1);

        if (!$fechaIngreso) {
            $startMonth = '2019-01';
        } else {
            $ingresoCarbon = Carbon::parse($fechaIngreso);
            if ($ingresoCarbon->lt($fechaLimite)) {
                $startMonth = '2019-01'; // Si es menor a 2019, forzamos 2019-01
            } else {
                $startMonth = $ingresoCarbon->format('Y-m');
            }
        }

        // Generamos la lista de meses desde el inicio calculado hasta el mes ACTUAL solamente
        $mesesAEvaluar = collect($this->generateMonthRange($startMonth, $currentMonthKey))
            ->unique()
            ->sort()
            ->values();

        $debts = collect();
        $thresholdOldDebt = now()->subMonth()->format('Y-m');
        $hasDisqualifyingOldDebt = false;

        // 5. Evaluar cada mes
        foreach ($mesesAEvaluar as $month) {
            $paymentData = $paymentsByMonth->get($month);
            $totalPaid = $paymentData ? (float) $paymentData->total_pagado : 0.0;

            // --- REGLA DE NEGOCIO: OBTENER LA TARIFA COMPLETA (Para sacar total e impuesto) ---
            if ($totalPaid == 0) {
                // CORRECCIÓN: Escenario 1: No hay pagos. Se cobra directamente la tarifa vigente actual.
                $applicableFee = $currentFee;
            } else {
                // Escenario 2: Pago parcial. Buscamos la tarifa que existía en la fecha del primer pago.
                $mesDeLaFechaDePago = Carbon::parse($paymentData->fecha_primer_pago)->format('Y-m');
                $applicableFee = $allFeesLookup->filter(fn($f, $k) => $k <= $mesDeLaFechaDePago)->last() ?? $currentFee;
            }

            $applicableFeeTotal = $applicableFee->total;
            $applicableFeeImpuesto = $applicableFee->impuesto ?? 0.00;

            // Aplicamos el recargo familiar al total de la cuota elegida
            $nominalTotal = $applicableFeeTotal * (1 + $surchargeMultiplier);
            $deudaNominalPendiente = $nominalTotal - $totalPaid;

            // Si la deuda está saldada (con un margen de 1 centavo por redondeos), pasamos al siguiente mes
            if (round($deudaNominalPendiente, 2) <= 0.009) {
                continue;
            }

            // --- REGLAS DE DESCUENTO (20%) ---
            if ($month < $thresholdOldDebt) {
                $hasDisqualifyingOldDebt = true;
            }

            $discountMultiplier = 0.0;
            $isCurrentMonth = ($month === $currentMonthKey);

            if (($isCurrentMonth && now()->day <= 5) && !$hasDisqualifyingOldDebt) {
                $discountMultiplier = 0.20;
            }

            // --- CÁLCULO DEL FACTOR Y EFECTIVO ---
            $factorConversion = (1 + $surchargeMultiplier) / (1 + $surchargeMultiplier - $discountMultiplier);
            $efectivoRestante = $deudaNominalPendiente / $factorConversion;

            // 6. Registrar la deuda estructurada
            $debts->push([
                'mes' => $month,
                'cuota_aplicada' => round($nominalTotal, 2),
                'impuesto' => round($applicableFeeImpuesto, 2),
                'total_pagado' => round($totalPaid, 2),
                'deuda_pendiente' => round($deudaNominalPendiente, 2),
                'efectivo_restante' => round($efectivoRestante, 3),
                'factor_conversion' => $factorConversion,
                'tiene_descuento' => $discountMultiplier > 0,
                'estado' => $totalPaid > 0 ? 'Pago Parcial' : 'Sin Pagar'
            ]);
        }

        return $debts;
    }

    /**
     * Verifica si el socio titular tiene un familiar "Hijo" mayor de 30 años.
     * Retorna 0.25 (25%) si es verdadero, o 0.00 si es falso.
     *
     * @param Partner $partner
     * @return float
     */
    private function calculateFamilySurcharge(Partner $partner): float
    {
        $hasAdultChild = $partner->dependents->contains(function ($dependent) {
            return strtolower($dependent->direccion) === 'hijo'
                && $dependent->age !== null
                && $dependent->age > 30;
        });

        return $hasAdultChild ? 0.25 : 0.00;
    }

    /**
     * Helper para generar el rango de meses entre dos fechas.
     *
     * @param string $start (Formato Y-m)
     * @param string $end (Formato Y-m)
     * @return array
     */
    private function generateMonthRange(string $start, string $end): array
    {
        $dates = [];
        $current = Carbon::parse($start . '-01');
        $last = Carbon::parse($end . '-01');

        while ($current->lte($last)) {
            $dates[] = $current->format('Y-m');
            $current->addMonth();
        }

        return $dates;
    }

    /** (Cambiar este metodo)
     * Obtiene el historial de pagos realizados por el socio en un rango de meses.
     * Busca desde el mes actual hasta el mes objetivo seleccionado (o viceversa).
     *
     * @param Partner $partner
     * @param string $targetMonth Formato 'Y-m' (ej. '2023-05')
     * @return Collection
     */
    public function getPaymentsBetweenCurrentAndTargetMonth(Partner $partner, string $targetMonth): Collection
    {
        $currentMonth = now()->format('Y-m');

        $startMonth = min($currentMonth, $targetMonth);
        $endMonth = max($currentMonth, $targetMonth);

        return HistoryPay::where('acc', $partner->acc)
            ->whereBetween('mes', [$startMonth, $endMonth])
            ->orderBy('mes', 'desc')
            ->orderBy('fecha', 'desc')
            ->get();
    }
}
