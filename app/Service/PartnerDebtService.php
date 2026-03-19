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
    /**
     * @param Partner $partner
     * @param array $paymentsList Ejemplo: [['mes' => '2026-03', 'monto' => 36.656], ['mes' => '2026-04', 'monto' => 20.00]]
     * @param array $paymentMetadata Datos extra (oper, operador, etc.)
     * @throws Exception
     */
    public function processPayments(Partner $partner, array $paymentsList, array $paymentMetadata = [])
    {
        // Extraemos los meses que el usuario intenta pagar (útil si está pagando adelantado)
        $mesesSolicitados = array_column($paymentsList, 'mes');

        // Obtenemos el estado de cuenta incluyendo los meses adelantados
        $statement = $this->getAccountStatement($partner, $mesesSolicitados)->keyBy('mes');

        \DB::beginTransaction();
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
                // Usamos round a 2 decimales para evitar problemas de coma flotante en la validación
                if (round($montoEfectivoEnviado, 2) > round($deudaData['efectivo_restante'], 2)) {
                    throw new Exception("El monto enviado ({$montoEfectivoEnviado}) para el mes {$mes} supera la deuda real que es de {$deudaData['efectivo_restante']}.");
                }

                // MAGIA: Escalamos el dinero en efectivo al valor nominal de la base de datos
                $montoAInsertar = $montoEfectivoEnviado * $deudaData['factor_conversion'];

                HistoryPay::create([
                    'acc' => $partner->acc,
                    'mes' => $mes,
                    'fecha' => now()->format('Y-m-d'), // Fecha real en que se hizo el pago
                    'monto' => round($montoAInsertar, 2), // El monto INFLADO para el motor de búsqueda
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

            \DB::commit();
            return true;

        } catch (Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }


    /**
     * Extrae todas las deudas pendientes de un socio con sus montos exactos,
     * limitando desde su fecha de ingreso hasta el mes actual (o adelantados).
     * Aplica reglas de inflación, tarifas congeladas por pagos parciales y descuentos.
     *
     * @param Partner $partner
     * @param array $mesesAdelantadosSolicitados Meses futuros que el socio desea pagar
     * @return Collection
     * @throws Exception
     */
    public function getAccountStatement(Partner $partner, array $mesesAdelantadosSolicitados = []): Collection
    {
        // 1. Calcular recargo familiar (Retorna 0.00 o 0.25)
        $surchargeMultiplier = $this->calculateFamilySurcharge($partner);

        // 2. Cargar TODAS las cuotas en memoria y ordenarlas por mes
        $allFeesLookup = Fee::all()->keyBy('mes')->sortKeys();

        $currentMonthKey = now()->format('Y-m');

        // Buscar la cuota vigente actual (la del mes actual o la más reciente hacia atrás)
        $currentFee = $allFeesLookup->filter(fn($fee, $key) => $key <= $currentMonthKey)->last();

        if (!$currentFee) {
            throw new Exception("No se encontró una cuota base configurada en el sistema.");
        }

        // 3. Agrupar historial de pagos del socio (Optimizando a una sola consulta)
        $paymentsByMonth = HistoryPay::where('acc', $partner->acc)
            ->selectRaw('mes, SUM(monto) as total_pagado, MIN(fecha) as fecha_primer_pago')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        // 4. Determinar el rango de meses a evaluar
        $startMonth = $partner->fecha_ingreso_validada ?: $allFeesLookup->keys()->first();

        // Generamos la lista de meses desde el ingreso hasta hoy, más los adelantados
        $mesesAEvaluar = collect($this->generateMonthRange($startMonth, $currentMonthKey))
            ->merge($mesesAdelantadosSolicitados)
            ->unique()
            ->sort()
            ->values();

        $debts = collect();
        $thresholdOldDebt = now()->subMonth()->format('Y-m'); // Mes anterior para evaluar morosidad
        $hasDisqualifyingOldDebt = false;

        // 5. Evaluar cada mes
        foreach ($mesesAEvaluar as $month) {
            $paymentData = $paymentsByMonth->get($month);
            $totalPaid = $paymentData ? (float) $paymentData->total_pagado : 0.0;

            // --- REGLA DE NEGOCIO: TARIFA ACTUAL VS TARIFA CONGELADA ---
            if ($totalPaid == 0) {
                // Escenario 1: No hay pagos. Se cobra a la tarifa actual (o a la futura si es un mes adelantado).
                $applicableFeeTotal = ($month > $currentMonthKey)
                    ? ($allFeesLookup->filter(fn($f, $k) => $k <= $month)->last()->total ?? $currentFee->total)
                    : $currentFee->total;
            } else {
                // Escenario 2: Pago parcial. Buscamos la tarifa que existía en la fecha del primer pago.
                $mesDeLaFechaDePago = Carbon::parse($paymentData->fecha_primer_pago)->format('Y-m');
                $historicalFee = $allFeesLookup->filter(fn($f, $k) => $k <= $mesDeLaFechaDePago)->last();

                $applicableFeeTotal = $historicalFee ? $historicalFee->total : $currentFee->total;
            }

            // Aplicamos el recargo familiar al total de la cuota elegida
            $nominalTotal = $applicableFeeTotal * (1 + $surchargeMultiplier);
            $deudaNominalPendiente = $nominalTotal - $totalPaid;

            // Si la deuda está saldada (con un margen de 1 centavo por redondeos), pasamos al siguiente mes
            if (round($deudaNominalPendiente, 2) <= 0.009) {
                continue;
            }

            // --- REGLAS DE DESCUENTO (20%) ---
            // Si el socio debe un mes anterior al mes pasado, pierde el derecho a descuento
            if ($month < $thresholdOldDebt) {
                $hasDisqualifyingOldDebt = true;
            }

            $discountMultiplier = 0.0;
            $isCurrentMonth = ($month === $currentMonthKey);
            $isFutureMonth = ($month > $currentMonthKey);

            if (($isFutureMonth || ($isCurrentMonth && now()->day <= 5)) && !$hasDisqualifyingOldDebt) {
                $discountMultiplier = 0.20;
            }

            // --- CÁLCULO DEL FACTOR Y EFECTIVO (Vital para el método de pago) ---
            $factorConversion = (1 + $surchargeMultiplier) / (1 + $surchargeMultiplier - $discountMultiplier);
            $efectivoRestante = $deudaNominalPendiente / $factorConversion;

            // 6. Registrar la deuda estructurada
            $debts->push([
                'mes' => $month,
                'cuota_aplicada' => round($nominalTotal, 2),        // Lo que cuesta para el sistema
                'total_pagado' => round($totalPaid, 2),             // Lo que ya ha dado
                'deuda_pendiente' => round($deudaNominalPendiente, 2), // Lo que falta completar (Nominal)

                // Nuevas llaves requeridas para evitar el Error 500
                'efectivo_restante' => round($efectivoRestante, 3), // Lo que realmente saca del bolsillo hoy
                'factor_conversion' => $factorConversion,           // Para que el pago infle el número
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
     * Evita bucles infinitos y genera un array limpio ['2024-01', '2024-02', ...]
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
}
