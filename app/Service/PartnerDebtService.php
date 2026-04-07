<?php

namespace App\Service;

use App\Models\Fee;
use App\Models\HistoryPay;
use App\Models\Partner;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PartnerDebtService
{
    public function getTitularDebtSummaryList(): array
    {
        $defaultStartMonth = '2019-01';
        $currentMonth = now()->format('Y-m');
        $endOfCurrentYearMonth = now()->endOfYear()->format('Y-m');

        $partners = $this->getEligibleTitularPartners();

        if ($partners->isEmpty()) {
            return [];
        }

        $feesByMonth = $this->buildFeeLookupByMonth($defaultStartMonth, $endOfCurrentYearMonth);
        $paymentsByAccAndMonth = $this->getPaymentsLookupByAccountAndMonth(
            $partners->pluck('acc')->all(),
            $defaultStartMonth,
            $endOfCurrentYearMonth
        );

        $result = [];
        $position = 1;

        foreach ($partners as $partner) {
            $startMonth = $this->resolveMembershipStartMonth($partner->ingreso, $defaultStartMonth);

            if ($startMonth > $endOfCurrentYearMonth) {
                $months = [];
            } else {
                $months = array_reverse($this->generateMonthRange($startMonth, $endOfCurrentYearMonth));
            }

            $deuda = [];
            $pagos = [];

            foreach ($months as $month) {
                $monthlyPayment = round((float) ($paymentsByAccAndMonth[$partner->acc][$month] ?? 0.0), 2);
                $monthlyBaseDebt = $month <= $currentMonth
                    ? round((float) ($feesByMonth[$month] ?? 0.0), 2)
                    : 0.0;

                $deuda[$month] = round($monthlyBaseDebt - $monthlyPayment, 2);
                $pagos[$month] = $monthlyPayment;
            }

            $result[(string) $position] = [
                'acc' => (int) $partner->acc,
                'total' => round(array_sum($deuda), 2),
                'deuda' => $deuda,
                'pagos' => $pagos,
            ];

            $position++;
        }

        return $result;
    }
    public function titularDebtSummaryByYear(int $year): array
    {
        $currentMonth = now()->format('Y-m');

        // 1. Creamos el intervalo estricto a partir de ese año, desde su inicio hasta su fin
        $startOfYear = "{$year}-01";
        $endOfYear = "{$year}-12";

        $partners = $this->getEligibleTitularPartners();

        if ($partners->isEmpty()) {
            return [];
        }

        // Limitamos las consultas de base de datos a ese año para optimizar
        $feesByMonth = $this->buildFeeLookupByMonth($startOfYear, $endOfYear);
        $paymentsByAccAndMonth = $this->getPaymentsLookupByAccountAndMonth(
            $partners->pluck('acc')->all(),
            $startOfYear,
            $endOfYear
        );

        $result = [];
        $position = 1;

        // 2. El array de meses se genera UNA SOLA VEZ para todo el año completo (12 meses fijos)
        // Usamos array_reverse para mantener tu lógica original (de diciembre hacia enero)
        $months = array_reverse($this->generateMonthRange($startOfYear, $endOfYear));

        foreach ($partners as $partner) {
            // Obtenemos cuándo ingresó realmente el socio
            $startMonth = $this->resolveMembershipStartMonth($partner->ingreso, '2019-01');

            $deuda = [];
            $pagos = [];

            foreach ($months as $month) {
                $monthlyPayment = round((float) ($paymentsByAccAndMonth[$partner->acc][$month] ?? 0.0), 2);

                // 3. Condición para la deuda base:
                // - Si el mes que estamos evaluando es ANTERIOR a su fecha de ingreso, la deuda es 0.0
                // - Si el mes está en el FUTURO (ej. pides 2026 pero estamos en abril), la deuda es 0.0
                // - Si el mes es válido, se le carga la cuota correspondiente.
                if ($month >= $startMonth && $month <= $currentMonth) {
                    $monthlyBaseDebt = round((float) ($feesByMonth[$month] ?? 0.0), 2);
                } else {
                    $monthlyBaseDebt = 0.0;
                }

                $deuda[$month] = round($monthlyBaseDebt - $monthlyPayment, 2);
                $pagos[$month] = $monthlyPayment;
            }

            $result[(string) $position] = [
                'acc' => (int) $partner->acc,
                'total' => round(array_sum($deuda), 2),
                'deuda' => $deuda,
                'pagos' => $pagos,
            ];
            $position++;
        }

        return $result;
    }
    /**
     * @param  array  $paymentsList  Ejemplo: [['mes' => '2026-03', 'monto' => 36.656], ['mes' => '2026-04', 'monto' => 20.00]]
     * @param  array  $paymentMetadata  Datos extra (oper, operador, etc.)
     *
     * @throws Exception
     */
    public function processPayments(Partner $partner, array $paymentsList, array $paymentMetadata = []): true
    {
        if (empty($paymentsList)) {
            return true;
        }
        // 1. Detectamos el mes más lejano que se intenta pagar
        $maxMonthToPay = collect($paymentsList)->max('mes');

        // 2. Obtenemos el estado de cuenta completo (Array)
        $accountStatementData = $this->getAccountStatement($partner, $maxMonthToPay);

        // 3. ENVOLVEMOS en collect() para forzar la Colección y luego aplicamos keyBy('mes')
        $statement = collect($accountStatementData['debts'])->keyBy('mes');

        DB::beginTransaction();

        try {
            foreach ($paymentsList as $pago) {
                $mes = $pago['mes'];
                $montoEfectivoEnviado = (float) $pago['monto'];

                if ($montoEfectivoEnviado <= 0) {
                    continue;
                }

                if (! $statement->has($mes)) {
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
     * Cotiza cuánto cuesta pagar N meses por adelantado incluyendo datos de hijos.
     *
     * @return array Contiene 'quotes' (Collection) e 'hijos_mayores' (array)
     *
     * @throws Exception
     */
    public function getAdvancePaymentsQuotes(Partner $partner, int $monthsToAdvance): array
    {
        if ($monthsToAdvance <= 0) {
            return ['quotes' => collect(), 'hijos_mayores' => []];
        }

        $currentMonthKey = now()->format('Y-m');
        $endFutureMonth = now()->addMonths($monthsToAdvance)->format('Y-m');

        // 1. Obtenemos el estado de cuenta (que ya trae los hijos y las deudas)
        $statementData = $this->getAccountStatement($partner, $endFutureMonth);

        // 2. Filtramos solo los meses que son estrictamente futuros
        $filteredQuotes = $statementData['debts']->filter(function ($debt) use ($currentMonthKey) {
            return $debt['mes'] > $currentMonthKey && $debt['deuda_pendiente'] > 0;
        })->values();

        // 3. Retornamos la estructura completa
        return [
            'quotes' => $filteredQuotes,
            'hijos_mayores' => $statementData['hijos_mayores'],
        ];
    }

    /**
     * Extrae todas las deudas pendientes de un socio y los hijos mayores de 30 años.
     *
     * @param  string|null  $endMonthLimit  Permite evaluar meses hacia el futuro (Formato Y-m)
     * @return array Retorna un array con las deudas ('debts') y los nombres de los hijos ('hijos_mayores')
     *
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

        $currentFee = $allFeesLookup->filter(fn ($fee, $key) => $key <= $currentMonthKey)->last();

        if (! $currentFee) {
            throw new Exception('No se encontró una cuota base configurada en el sistema.');
        }

        $paymentsByMonth = HistoryPay::where('acc', $partner->acc)
            ->selectRaw('mes, SUM(monto) as total_pagado, MIN(fecha) as fecha_primer_pago')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        $fechaIngreso = $partner->fecha_ingreso ?? $partner->fecha_ingreso_validada;
        $fechaLimite = Carbon::create(2019, 1, 1);

        if (! $fechaIngreso) {
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
                $applicableFee = $allFeesLookup->filter(fn ($f, $k) => $k <= $mesDeLaFechaDePago)->last() ?? $currentFee;
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
            if (($isCurrentMonthValid || $isFutureMonth) && ! $hasDisqualifyingOldDebt) {
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
                'estado' => $totalPaid > 0 ? 'Pago Parcial' : ($month > $currentMonthKey ? 'Por Adelantar' : 'Sin Pagar'),
            ]);
        }

        // 2. Retornamos ambas cosas en un array
        return [
            'debts' => $debts,
            'hijos_mayores' => $nombresHijosMayores,
        ];
    }

    /**
     * Verifica cuántos hijos mayores de 30 años tiene el socio y extrae sus nombres.
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
            'names' => $adultChildren->pluck('nombre')->toArray(),
        ];
    }

    private function generateMonthRange(string $start, string $end): array
    {
        $dates = [];
        $current = Carbon::parse($start.'-01');
        $last = Carbon::parse($end.'-01');

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
     * @param  string  $targetMonth  Formato 'Y-m' (ej. '2023-05')
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

    private function getEligibleTitularPartners(): Collection
    {
        return Partner::query()
            ->holders()
            ->select(['acc', 'ingreso', 'nombre'])
            ->whereRaw("UPPER(COALESCE(nombre, '')) NOT LIKE ?", ['%TESORERIA%'])
            ->whereRaw("UPPER(COALESCE(nombre, '')) NOT LIKE ?", ['%DESOCUPADO%'])
            ->orderBy('acc')
            ->get();
    }

    private function buildFeeLookupByMonth(string $startMonth, string $endMonth): array
    {
        $fees = Fee::query()
            ->select(['mes', 'cuota', 'impuesto'])
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $lookup = [];
        $activeFee = 0.0;

        foreach ($this->generateMonthRange($startMonth, $endMonth) as $month) {
            if ($fees->has($month)) {
                $activeFee = round((float) $fees->get($month)->total, 2);
            }

            $lookup[$month] = $activeFee;
        }

        return $lookup;
    }

    private function getPaymentsLookupByAccountAndMonth(array $accounts, string $startMonth, string $endMonth): array
    {
        if (empty($accounts)) {
            return [];
        }

        $payments = HistoryPay::query()
            ->selectRaw('acc, mes, SUM(monto) as total_pagado')
            ->whereIn('acc', $accounts)
            ->whereBetween('mes', [$startMonth, $endMonth])
            ->groupBy('acc', 'mes')
            ->get();

        $lookup = [];

        foreach ($payments as $payment) {
            $lookup[(int) $payment->acc][$payment->mes] = round((float) $payment->total_pagado, 2);
        }

        return $lookup;
    }

    private function resolveMembershipStartMonth(?string $ingreso, string $defaultStartMonth): string
    {
        $rawIngreso = is_string($ingreso) ? trim($ingreso) : '';

        if ($rawIngreso === '' || $rawIngreso === '-') {
            return $defaultStartMonth;
        }

        try {
            $parsedMonth = Carbon::parse($rawIngreso)->format('Y-m');
        } catch (Exception) {
            return $defaultStartMonth;
        }

        return $parsedMonth < $defaultStartMonth ? $defaultStartMonth : $parsedMonth;
    }
}
