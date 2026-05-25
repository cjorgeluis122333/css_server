<?php

namespace App\Service;

use App\Enum\PartnerCategory;
use App\Models\Fee;
use App\Models\HistoryPay;
use App\Models\Partner;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class HistoryPayService
{

    /**
     * Crea un nuevo registro de historial.
     */
    public function createHistory(array $data): HistoryPay
    {
        try {
            return HistoryPay::create($data);
        } catch (Exception $e) {
            Log::error('Error al crear historial de pago: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener el historial de un socio específico por su cuenta (acc)
     */
    public function getHistoryByAccount(int $acc)
    {
        return HistoryPay::where('acc', $acc)->orderBy('ind', 'desc')->get();
    }

    /**
     * Calcula la deuda acumulada del socio al momento de cada pago registrado.
     *
     * Para cada registro de HistoryPay, la deuda se define como:
     *   deuda = (cuotas acumuladas desde el inicio del socio hasta el mes pagado)
     *           − (total pagado hasta la fecha en que se realizó ese pago, inclusive)
     *
     * Una deuda negativa indica crédito a favor del socio (pagó más de lo adeudado
     * hasta ese mes). Esto permite visualizar correctamente los pagos adelantados:
     * si el socio pagó hasta 2027-01 en enero de 2026, el registro de 2026-01 mostrará
     * un saldo negativo que representa el crédito por los meses futuros ya cancelados.
     *
     * Importante: todos los pagos realizados el mismo día se agrupan antes de calcular
     * la deuda de cualquier registro de esa fecha, reflejando que son una sola operación.

     * Calcula la deuda acumulada del socio al momento de cada pago registrado.
     *
     * @return array<int, float> Mapa de ind -> deuda
     */
    public function computeRunningDebtMap(int $acc): array
    {
        // 1. Determinar el mes de inicio del socio titular
        $partner = Partner::where('acc', $acc)
            ->where('categoria', PartnerCategory::TITULAR->value)
            ->with('dependents')
            ->first();

        $startMonth = '2019-01';
        $surchargeMultiplier = 0.0;

        if ($partner) {
            $rawIngreso = trim((string) ($partner->ingreso ?? ''));

            if ($rawIngreso !== '' && $rawIngreso !== '-') {
                try {
                    $ingresoCarbon = Carbon::parse($rawIngreso);

                    if ($ingresoCarbon->year >= 2019) {
                        $startMonth = $ingresoCarbon->format('Y-m');
                    }
                } catch (Exception $e) {
                    // Usar el mes de inicio por defecto
                }
            }

            $childrenData = $this->getAdultChildrenData($partner);
            $surchargeMultiplier = $childrenData['multiplier'] ?? 0.0;
        }

        // 2. Determinar el mes final
        $maxMesPaid = HistoryPay::where('acc', $acc)->max('mes') ?? now()->format('Y-m');
        $endMonth = $maxMesPaid > now()->format('Y-m') ? $maxMesPaid : now()->format('Y-m');

        // 3. Construir el lookup de cuota acumulada por mes
        $allFees = Fee::all()->sortBy('mes')->keyBy('mes');
        $months = $this->generateMonthRange($startMonth, $endMonth);

        $currentFeeValue = 0.0;
        $cumFees = [];
        $cumulativeFee = 0.0;

        foreach ($months as $month) {
            if ($allFees->has($month)) {
                $currentFeeValue = (float) $allFees->get($month)->total * (1 + $surchargeMultiplier);
            }

            $cumulativeFee += $currentFeeValue;
            $cumFees[$month] = round($cumulativeFee, 2);
        }

        // 4. Obtener todos los registros de pago en ORDEN CRONOLÓGICO ESTRICTO
        // Esto es vital para que la reducción de la deuda sea paso a paso.
        $payments = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'asc')
            ->orderBy('ind', 'asc')
            ->get(['ind', 'fecha', 'mes', 'monto']);

        // 5. Calcular la deuda de cada registro secuencialmente
        $result = [];
        $runningPaid = 0.0;

        foreach ($payments as $payment) {
            // Acumulamos el pago actual
            $runningPaid += (float) $payment->monto;

            // EXTRAEMOS EL MES DE LA FECHA DE PAGO (El "Cuándo" físico de la transacción)
            $paymentDateMonth = Carbon::parse($payment->fecha ?? $payment->mes)->format('Y-m');

            // Solo cobramos las cuotas que se habían generado hasta el momento en que fue a pagar
            $totalFeesThrough = $cumFees[$paymentDateMonth] ?? 0.0;

            // Deuda en este punto específico de la historia
            $result[(int) $payment->ind] = round($totalFeesThrough - $runningPaid, 2);
        }

        return $result;
    }
    /**
     * Calcula, para cada registro de pago, el total acumulado pagado para ese mes
     * y la cantidad de abonos registrados para ese mes hasta ese registro.
     *
     * Los registros se procesan en orden cronológico (fecha asc, ind asc), de modo que
     * el primero en haber sido registrado para un mes tiene abono_count=1 y pago=su monto,
     * el segundo acumula sobre el primero, y así sucesivamente.
     *
     * Ejemplo para mes=2026-12 con tres pagos de $2, $6 y $2:
     *   ind=X1 → pago=2,  abono_count=1
     *   ind=X2 → pago=8,  abono_count=2
     *   ind=X3 → pago=10, abono_count=3
     *
     * @return array<int, array{pago: float, abono_count: int}> Mapa de ind -> [pago, abono_count]
     */
    public function computeMonthlyPaymentMap(int $acc): array
    {
        $payments = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'asc')
            ->orderBy('ind', 'asc')
            ->get(['ind', 'fecha', 'mes', 'monto']);

        $accumulatedByMes = []; // mes -> ['total' => float, 'count' => int]
        $result = [];

        foreach ($payments as $payment) {
            $mes = $payment->mes;

            if (! isset($accumulatedByMes[$mes])) {
                $accumulatedByMes[$mes] = ['total' => 0.0, 'count' => 0];
            }

            $accumulatedByMes[$mes]['total'] += (float) $payment->monto;
            $accumulatedByMes[$mes]['count']++;

            $result[(int) $payment->ind] = [
                'pago'        => round($accumulatedByMes[$mes]['total'], 2),
                'abono_count' => $accumulatedByMes[$mes]['count'],
            ];
        }

        return $result;
    }

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
}
