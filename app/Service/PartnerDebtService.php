<?php

namespace App\Service;

use App\Models\Fee;
use App\Models\Partner;
use App\Models\HistoryPay;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Collection;

class PartnerDebtService
{
    /**
     * @param Partner $partner
     * @param array $paymentsList Ejemplo: [['mes' => '2026-03', 'monto' => 36.656], ['mes' => '2026-04', 'monto' => 20.00]]
     * @param array $paymentMetadata Datos extra (oper, operador, etc.)
     * @throws Exception
     */
    public function processPayments(Partner $partner, array $paymentsList, array $paymentMetadata = [])
    {
        if (empty($paymentsList)) {
            return true;
        }

        // 1. Detectamos el mes más lejano que se intenta pagar (puede ser un mes futuro)
        $maxMonthToPay = collect($paymentsList)->max('mes');

        // 2. Generamos el estado de cuenta "estirándolo" hasta ese mes máximo
        $statement = $this->getAccountStatement($partner, $maxMonthToPay)->keyBy('mes');

        DB::beginTransaction();
        try {
            foreach ($paymentsList as $pago) {
                $mes = $pago['mes'];
                $montoEfectivoEnviado = (float)$pago['monto'];

                if ($montoEfectivoEnviado <= 0) continue;

                if (!$statement->has($mes)) {
                    throw new Exception("El mes {$mes} no presenta deudas o no es válido para este socio.");
                }

                $deudaData = $statement->get($mes);

                // REGLA: El pago no puede superar la deuda del mes
                if (round($montoEfectivoEnviado, 2) > round($deudaData['efectivo_restante'], 2)) {
                    throw new Exception("El monto enviado ({$montoEfectivoEnviado}) para el mes {$mes} supera la deuda real que es de {$deudaData['efectivo_restante']}.");
                }

                // MAGIA: Escalamos el dinero en efectivo al valor nominal
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
     * NUEVO MÉTODO: Cotiza exactamente cuánto cuesta pagar N meses por adelantado
     * tomando como base la cuota actual.
     *
     * @param Partner $partner
     * @param int $monthsToAdvance Cantidad de meses a pagar a futuro
     * @return Collection
     */
    public function getAdvancePaymentsQuotes(Partner $partner, int $monthsToAdvance): Collection
    {
        if ($monthsToAdvance <= 0) {
            return collect();
        }

        $currentMonthKey = now()->format('Y-m');

        // Calculamos hasta qué mes futuro vamos a proyectar
        $endFutureMonth = now()->addMonths($monthsToAdvance)->format('Y-m');

        // Reutilizamos toda la lógica de getAccountStatement para obtener deudas futuras
        $fullStatement = $this->getAccountStatement($partner, $endFutureMonth);

        // Filtramos y retornamos SOLO los meses que son estrictamente futuros y tienen deuda
        return $fullStatement->filter(function ($debt) use ($currentMonthKey) {
            return $debt['mes'] > $currentMonthKey && $debt['deuda_pendiente'] > 0;
        })->values();
    }

    /**
     * Extrae todas las deudas pendientes de un socio y los hijos mayores de 30 años.
     *
     * @param Partner $partner
     * @param string|null $endMonthLimit Permite evaluar meses hacia el futuro (Formato Y-m)
     * @return array Retorna un array con las deudas ('debts') y los nombres de los hijos ('hijos_mayores')
     * @throws Exception
     */
    public function getAccountStatement(Partner $partner, ?string $endMonthLimit = null): array
    {
        // 1. Obtenemos la información de los hijos (Multiplicador y Nombres)
        $childrenData = $this->getAdultChildrenData($partner);
        $surchargeMultiplier = $childrenData['multiplier'];
        $nombresHijosMayores = $childrenData['names'];

        $allFeesLookup = Fee::all()->keyBy('mes')->sortKeys();
        $currentMonthKey = now()->format('Y-m');

        $currentFee = $allFeesLookup->filter(fn($fee, $key) => $key <= $currentMonthKey)->last();

        if (!$currentFee) {
            throw new Exception("No se encontró una cuota base configurada en el sistema.");
        }

        $paymentsByMonth = HistoryPay::where('acc', $partner->acc)
            ->selectRaw('mes, SUM(monto) as total_pagado, MIN(fecha) as fecha_primer_pago')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        $fechaIngreso = $partner->fecha_ingreso ?? $partner->fecha_ingreso_validada;
        $fechaLimite = Carbon::create(2019, 1, 1);

        if (!$fechaIngreso) {
            $startMonth = '2019-01';
        } else {
            $ingresoCarbon = Carbon::parse($fechaIngreso);
            if ($ingresoCarbon->lt($fechaLimite)) {
                $startMonth = '2019-01';
            } else {
                $startMonth = $ingresoCarbon->format('Y-m');
            }
        }

        // LÓGICA MODIFICADA: Si recibimos un mes límite futuro, extendemos el rango hasta allá
        $evalEndMonth = ($endMonthLimit && $endMonthLimit > $currentMonthKey)
            ? $endMonthLimit
            : $currentMonthKey;

        $mesesAEvaluar = collect($this->generateMonthRange($startMonth, $evalEndMonth))
            ->unique()
            ->sort()
            ->values();

        $debts = collect();
        $thresholdOldDebt = now()->subMonth()->format('Y-m');
        $hasDisqualifyingOldDebt = false;

        foreach ($mesesAEvaluar as $month) {
            $paymentData = $paymentsByMonth->get($month);
            $totalPaid = $paymentData ? (float) $paymentData->total_pagado : 0.0;

            if ($totalPaid == 0) {
                // Si es un mes futuro, utilizará correctamente la cuota actual vigente
                $applicableFee = $currentFee;
            } else {
                $mesDeLaFechaDePago = Carbon::parse($paymentData->fecha_primer_pago)->format('Y-m');
                $applicableFee = $allFeesLookup->filter(fn($f, $k) => $k <= $mesDeLaFechaDePago)->last() ?? $currentFee;
            }

            $applicableFeeTotal = $applicableFee->total;
            $applicableFeeImpuesto = $applicableFee->impuesto ?? 0.00;

            // El recargo ahora puede ser 0.25, 0.50, 0.75, etc., dependiendo de la cantidad de hijos
            $nominalTotal = $applicableFeeTotal * (1 + $surchargeMultiplier);
            $deudaNominalPendiente = $nominalTotal - $totalPaid;

            if (round($deudaNominalPendiente, 2) <= 0.009) {
                continue;
            }

            // --- LÓGICA DE DESCUENTO ACTUALIZADA ---

            // Si el mes que evaluamos es más viejo que el mes pasado, se marca como deuda vieja
            if ($month < $thresholdOldDebt) {
                $hasDisqualifyingOldDebt = true;
            }

            $discountMultiplier = 0.0;

            $isCurrentMonthValid = ($month === $currentMonthKey && now()->day <= 5);
            $isFutureMonth = ($month > $currentMonthKey);

            // Se aplica el 20% si es pronto pago (mes actual <= día 5 o mes futuro)
            // y no arrastra deudas anteriores al mes pasado.
            if (($isCurrentMonthValid || $isFutureMonth) && !$hasDisqualifyingOldDebt) {
                $discountMultiplier = 0.20;
            }

            // Calculamos el factor de conversión basado en recargos y descuentos
            $factorConversion = (1 + $surchargeMultiplier) / (1 + $surchargeMultiplier - $discountMultiplier);
            $efectivoRestante = $deudaNominalPendiente / $factorConversion;

            $debts->push([
                'mes' => $month,
                'cuota_aplicada' => round($nominalTotal, 2),
                'impuesto' => round($applicableFeeImpuesto, 2),
                'total_pagado' => round($totalPaid, 2),
                'deuda_pendiente' => round($deudaNominalPendiente, 2),
                'efectivo_restante' => round($efectivoRestante, 3),
                'factor_conversion' => $factorConversion,
                'tiene_descuento' => $discountMultiplier > 0,
                'estado' => $totalPaid > 0 ? 'Pago Parcial' : ($month > $currentMonthKey ? 'Por Adelantar' : 'Sin Pagar')
            ]);
        }

        // 2. Retornamos ambas cosas en un array
        return [
            'debts' => $debts,
            'hijos_mayores' => $nombresHijosMayores
        ];
    }

    /**
     * Verifica cuántos hijos mayores de 30 años tiene el socio y extrae sus nombres.
     *
     * @param Partner $partner
     * @return array
     */
    private function getAdultChildrenData(Partner $partner): array
    {
        // Filtramos para obtener la colección completa de hijos que cumplen la condición
        $adultChildren = $partner->dependents->filter(function ($dependent) {
            return strtolower(trim($dependent->direccion)) === 'hijo'
                && $dependent->age !== null
                && $dependent->age > 30;
        });

        return [
            // Multiplicamos 0.25 por la cantidad exacta de hijos encontrados
            'multiplier' => $adultChildren->count() * 0.25,

            // Extraemos solo los nombres (asumiendo que el campo en BD se llama 'nombre')
            // Si tu campo se llama 'name' u otra cosa, cámbialo aquí dentro del pluck()
            'names' => $adultChildren->pluck('nombre')->toArray()
        ];
    }

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
