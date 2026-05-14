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
     * Para cada registro de HistoryPay (ordenado cronológicamente), la deuda
     * se define como:
     *   deuda = (cuotas acumuladas desde el inicio del socio hasta el mes del pago)
     *           - (suma de todos los pagos realizados hasta ese registro, inclusive)
     *
     * @return array<int, float> Mapa de ind -> deuda
     */
    public function computeRunningDebtMap(int $acc): array
    {
        // 1. Determinar el mes de inicio del socio titular
        $partner = Partner::where('acc', $acc)
            ->where('categoria', PartnerCategory::TITULAR->value)
            ->first();

        $startMonth = '2019-01';

        if ($partner) {
            $rawIngreso = trim((string) ($partner->ingreso ?? ''));

            if ($rawIngreso !== '' && $rawIngreso !== '-') {
                try {
                    $ingresoCarbon = Carbon::parse($rawIngreso);

                    if ($ingresoCarbon->year >= 2019) {
                        $startMonth = $ingresoCarbon->format('Y-m');
                    }
                } catch (Exception) {
                    // Usar el mes de inicio por defecto si la fecha no es parseable
                }
            }
        }

        // 2. Construir el lookup de cuota por mes (con arrastre del último valor conocido)
        $currentMonthKey = now()->format('Y-m');
        $allFees = Fee::all()->sortBy('mes')->keyBy('mes');
        $months = $this->generateMonthRange($startMonth, $currentMonthKey);

        $currentFeeValue = 0.0;
        $cumFees = [];
        $cumulativeFee = 0.0;

        foreach ($months as $month) {
            if ($allFees->has($month)) {
                $currentFeeValue = (float) $allFees->get($month)->total;
            }

            $cumulativeFee += $currentFeeValue;
            $cumFees[$month] = round($cumulativeFee, 2);
        }

        // 3. Obtener todos los registros de pago en orden cronológico
        $payments = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'asc')
            ->orderBy('ind', 'asc')
            ->get();

        // 4. Calcular la deuda acumulada tras cada pago
        $result = [];
        $cumulativePaid = 0.0;

        foreach ($payments as $payment) {
            $cumulativePaid += (float) $payment->monto;

            // Usamos el mes en que se realizó el pago (fecha) como base para acumular cuotas,
            // no el mes que se está pagando (mes). Así, pagar una deuda vieja en 2026
            // descuenta del total de deuda actual (cuotas hasta hoy − pagos hasta este registro).
            $fechaMonth = $payment->fecha
                ? Carbon::parse($payment->fecha)->format('Y-m')
                : $payment->mes;

            $totalFeesThrough = $cumFees[$fechaMonth] ?? 0.0;
            $result[(int) $payment->ind] = round($totalFeesThrough - $cumulativePaid, 2);
        }

        return $result;
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
