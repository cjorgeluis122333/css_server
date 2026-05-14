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
        // 1. Determinar el socio
        $partner = Partner::where('acc', $acc)
            ->where('categoria', PartnerCategory::TITULAR->value)
            ->first();

        $startMonth = '2019-01';
        $surchargeMultiplier = 0.0;

        if ($partner) {
            // Misma validación estricta de fecha de ingreso que getAccountStatement
            $fechaIngreso = $partner->fecha_ingreso ?? $partner->fecha_ingreso_validada;
            $fechaLimite = Carbon::create(2019, 1, 1);

            if ($fechaIngreso) {
                try {
                    $ingresoCarbon = Carbon::parse($fechaIngreso);
                    if ($ingresoCarbon->lt($fechaLimite)) {
                        $startMonth = '2019-01';
                    } else {
                        $startMonth = $ingresoCarbon->format('Y-m');
                    }
                } catch (Exception) {
                    $startMonth = '2019-01';
                }
            }

            // Multiplicador de hijos
            $childrenData = $this->getAdultChildrenData($partner);
            $surchargeMultiplier = $childrenData['multiplier'] ?? 0.0;
        }

        $allFeesLookup = Fee::all()->sortBy('mes')->keyBy('mes');

        // 2. Obtener todos los registros de pago en orden cronológico
        $payments = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'asc')
            ->orderBy('ind', 'asc')
            ->get();

        $result = [];

        // Mantendremos el estado de los pagos organizados por mes
        $paymentsByMonth = [];
        $firstPaymentDateByMonth = [];

        foreach ($payments as $payment) {
            $mesPagado = $payment->mes;
            $fechaPago = $payment->fecha ? Carbon::parse($payment->fecha)->format('Y-m') : $mesPagado;

            // Registrar el pago en su mes correspondiente (simulando el GROUP BY de getAccountStatement)
            if (!isset($paymentsByMonth[$mesPagado])) {
                $paymentsByMonth[$mesPagado] = 0.0;
                $firstPaymentDateByMonth[$mesPagado] = $fechaPago; // MIN(fecha)
            }
            $paymentsByMonth[$mesPagado] += (float) $payment->monto;

            // El rango evaluado al momento del pago es desde el inicio hasta el mes en que se realiza el pago
            $monthsToEvaluate = $this->generateMonthRange($startMonth, $fechaPago);

            $totalDebtAtThisMoment = 0.0;
            $currentFeeAtThisMoment = $allFeesLookup->filter(fn ($f, $k) => $k <= $fechaPago)->last();

            // Simulamos el "estado de cuenta" a la fecha de este pago específico
            foreach ($monthsToEvaluate as $m) {
                $totalPaidForM = $paymentsByMonth[$m] ?? 0.0;

                if ($totalPaidForM == 0) {
                    // Mes sin pagos: Se valora a la cuota "actual" (la del momento de este pago)
                    $applicableFee = $currentFeeAtThisMoment;
                } else {
                    // Mes con pagos: Se bloquea el precio a la fecha en que se hizo su PRIMER pago
                    $fechaPrimerPagoM = $firstPaymentDateByMonth[$m];
                    $applicableFee = $allFeesLookup->filter(fn ($f, $k) => $k <= $fechaPrimerPagoM)->last() ?? $currentFeeAtThisMoment;
                }

                if (! $applicableFee) {
                    continue;
                }

                $nominalTotal = $applicableFee->total * (1 + $surchargeMultiplier);
                $deudaMes = $nominalTotal - $totalPaidForM;

                // getAccountStatement suma solo las deudas positivas y omite si está pagado o hay saldo a favor
                if (round($deudaMes, 2) > 0.009) {
                    $totalDebtAtThisMoment += $deudaMes;
                }
            }

            $result[(int) $payment->ind] = round($totalDebtAtThisMoment, 2);
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
