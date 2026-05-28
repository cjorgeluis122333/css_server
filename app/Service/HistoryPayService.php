<?php

namespace App\Service;

use App\Enum\PartnerCategory;
use App\Models\Fee;
use App\Models\HistoryPay;
use App\Models\Partner;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
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
     * Obtener el historial de un socio especifico por su cuenta (acc).
     */
    public function getHistoryByAccount(int $acc): EloquentCollection
    {
        return HistoryPay::where('acc', $acc)->orderBy('ind', 'desc')->get();
    }

    /**
     * Obtener el historial paginado de un socio, ordenado cronologicamente descendente.
     * Usa doble ordenacion para desempatar registros con la misma fecha.
     */
    public function getHistoryByAccountPaginated(int $acc, int $perPage): LengthAwarePaginator
    {
        return HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'desc')
            ->orderBy('ind', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener todos los pagos realizados por un socio hasta un mes determinado (inclusive).
     *
     * Filtra los registros cuyo campo `mes` sea menor o igual al mes indicado,
     * ordenados cronologicamente ascendente.
     *
     * @param  int  $acc  Numero de accion del socio.
     * @param  string  $mes  Mes limite en formato Y-m (ej: 2026-05).
     * @return Collection<int, array{fecha: string|null, descripcion: string|null, recibo: string|null, operador: string|null, monto: string}>
     */
    public function getPaymentsUpToMonth(int $acc, string $mes): Collection
    {
        return HistoryPay::query()
            ->where('acc', $acc)
            ->where('mes', $mes)
            ->orderBy('mes')
            ->orderBy('ind')
            ->select(['fecha', 'descript', 'resibo', 'operador', 'monto'])
            ->get()
            ->map(fn (HistoryPay $record) => [
                'fecha' => $record->fecha,
                'descripcion' => $record->descript,
                'recibo' => $record->resibo,
                'operador' => $record->operador,
                'monto' => $record->monto,
            ]);
    }

    /**
     * Calcula la deuda del rango asociado a cada pago registrado.
     *
     * @return array<int, float> Mapa de ind -> deuda
     */
    public function computeRunningDebtMap(int $acc): array
    {
        $partner = Partner::where('acc', $acc)
            ->where('categoria', PartnerCategory::TITULAR->value)
            ->with('dependents')
            ->first();

        $startMonth = $partner
            ? $this->resolveMembershipStartMonth($partner->ingreso, '2019-01')
            : '2019-01';

        $childrenData = $partner ? $this->getAdultChildrenData($partner) : ['multiplier' => 0.0];
        $surchargeMultiplier = $childrenData['multiplier'] ?? 0.0;

        $payments = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'asc')
            ->orderBy('ind', 'asc')
            ->get(['ind', 'fecha', 'mes', 'monto']);

        if ($payments->isEmpty()) {
            return [];
        }

        $allFees = Fee::query()
            ->select(['mes', 'cuota', 'impuesto'])
            ->orderBy('mes')
            ->get()
            ->keyBy('mes')
            ->sortKeys();

        $firstPaymentDateByMonth = [];
        $accumulatedByMonth = [];
        $result = [];

        foreach ($payments as $payment) {
            $paymentMonth = (string) $payment->mes;

            if (! $this->isValidMonthKey($paymentMonth)) {
                $result[(int) $payment->ind] = 0.0;

                continue;
            }

            $paymentDateMonth = $this->resolvePaymentDateMonth($payment->fecha, $paymentMonth);

            if (! isset($firstPaymentDateByMonth[$paymentMonth])) {
                $firstPaymentDateByMonth[$paymentMonth] = $this->normalizeLedgerDate($payment->fecha, $paymentMonth);
            }

            $accumulatedByMonth[$paymentMonth] = ($accumulatedByMonth[$paymentMonth] ?? 0.0) + (float) $payment->monto;

            if ($paymentMonth > $paymentDateMonth) {
                $result[(int) $payment->ind] = $this->calculateAdvanceBalance(
                    $paymentMonth,
                    $paymentDateMonth,
                    $accumulatedByMonth,
                    $firstPaymentDateByMonth,
                    $allFees,
                    $surchargeMultiplier
                );

                continue;
            }

            $rangeStartMonth = max($paymentMonth, $startMonth);

            $result[(int) $payment->ind] = $this->calculatePendingDebtForRange(
                $rangeStartMonth,
                $paymentDateMonth,
                $accumulatedByMonth,
                $firstPaymentDateByMonth,
                $allFees,
                $surchargeMultiplier
            );
        }

        return $result;
    }

    /**
     * Calcula, para cada registro de pago, el total acumulado pagado para ese mes
     * y la cantidad de abonos registrados para ese mes hasta ese registro.
     *
     * @return array<int, array{pago: float, abono_count: int}> Mapa de ind -> [pago, abono_count]
     */
    public function computeMonthlyPaymentMap(int $acc): array
    {
        $payments = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'asc')
            ->orderBy('ind', 'asc')
            ->get(['ind', 'fecha', 'mes', 'monto']);

        $accumulatedByMes = [];
        $result = [];

        foreach ($payments as $payment) {
            $mes = $payment->mes;

            if (! isset($accumulatedByMes[$mes])) {
                $accumulatedByMes[$mes] = ['total' => 0.0, 'count' => 0];
            }

            $accumulatedByMes[$mes]['total'] += (float) $payment->monto;
            $accumulatedByMes[$mes]['count']++;

            $result[(int) $payment->ind] = [
                'pago' => round($accumulatedByMes[$mes]['total'], 2),
                'abono_count' => $accumulatedByMes[$mes]['count'],
            ];
        }

        return $result;
    }

    private function getAdultChildrenData(Partner $partner): array
    {
        $adultChildren = $partner->dependents->filter(function ($dependent) {
            return strtolower(trim($dependent->direccion)) === 'hijo'
                && $dependent->age !== null
                && $dependent->age > 30;
        });

        return [
            'multiplier' => $adultChildren->count() * 0.25,
            'names' => $adultChildren->pluck('nombre')->toArray(),
        ];
    }

    private function normalizeLedgerDate(?string $date, ?string $month): string
    {
        try {
            return Carbon::parse($date ?: "{$month}-01")->format('Y-m-d');
        } catch (Exception) {
            return Carbon::parse(($month ?: now()->format('Y-m')).'-01')->format('Y-m-d');
        }
    }

    private function resolvePaymentDateMonth(?string $date, string $fallbackMonth): string
    {
        return Carbon::parse($this->normalizeLedgerDate($date, $fallbackMonth))->format('Y-m');
    }

    private function resolveMonthlyFee(Collection $fees, string $referenceMonth): float
    {
        $fee = $fees
            ->filter(fn (Fee $fee, string $month): bool => $month <= $referenceMonth)
            ->last();

        return $fee ? round((float) $fee->total, 2) : 0.0;
    }

    private function calculatePendingDebtForRange(
        string $startMonth,
        string $endMonth,
        array $paymentsByMonth,
        array $firstPaymentDateByMonth,
        Collection $fees,
        float $surchargeMultiplier
    ): float {
        if ($startMonth > $endMonth) {
            return 0.0;
        }

        $debt = 0.0;

        foreach ($this->generateMonthRange($startMonth, $endMonth) as $month) {
            $monthlyDebt = $this->resolveMonthlyDebt(
                $month,
                $paymentsByMonth,
                $firstPaymentDateByMonth,
                $fees,
                $endMonth,
                $surchargeMultiplier
            );

            if ($monthlyDebt > 0.009) {
                $debt += $monthlyDebt;
            }
        }

        return round($debt, 2);
    }

    private function calculateAdvanceBalance(
        string $targetMonth,
        string $currentMonth,
        array $paymentsByMonth,
        array $firstPaymentDateByMonth,
        Collection $fees,
        float $surchargeMultiplier
    ): float {
        $advanceBalance = 0.0;

        foreach ($this->generateMonthRange($currentMonth, $targetMonth) as $month) {
            if ($month === $currentMonth) {
                $currentMonthDebt = $this->resolveMonthlyDebt(
                    $month,
                    $paymentsByMonth,
                    $firstPaymentDateByMonth,
                    $fees,
                    $currentMonth,
                    $surchargeMultiplier
                );

                $advanceBalance += max(0.0, $currentMonthDebt * -1);

                continue;
            }

            $advanceBalance += (float) ($paymentsByMonth[$month] ?? 0.0);
        }

        return round($advanceBalance * -1, 2);
    }

    private function resolveMonthlyDebt(
        string $month,
        array $paymentsByMonth,
        array $firstPaymentDateByMonth,
        Collection $fees,
        string $currentMonth,
        float $surchargeMultiplier
    ): float {
        $totalPaid = (float) ($paymentsByMonth[$month] ?? 0.0);
        $referenceMonth = $currentMonth;

        if ($totalPaid > 0 && isset($firstPaymentDateByMonth[$month])) {
            $referenceMonth = Carbon::parse($firstPaymentDateByMonth[$month])->format('Y-m');
        }

        $monthlyFee = $this->resolveMonthlyFee($fees, $referenceMonth);
        $nominalTotal = $monthlyFee * (1 + $surchargeMultiplier);

        return round($nominalTotal - $totalPaid, 2);
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

    private function isValidMonthKey(string $month): bool
    {
        return preg_match('/^\d{4}-\d{2}$/', $month) === 1;
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
