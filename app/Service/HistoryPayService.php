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
     * Calcula la deuda restante del mes aplicado en cada pago.
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

        $currentMonth = now()->format('Y-m');

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

            if (! isset($firstPaymentDateByMonth[$paymentMonth])) {
                $firstPaymentDateByMonth[$paymentMonth] = $this->normalizeLedgerDate($payment->fecha, $paymentMonth);
            }

            $accumulatedByMonth[$paymentMonth] = ($accumulatedByMonth[$paymentMonth] ?? 0.0) + (float) $payment->monto;

            if ($paymentMonth > $currentMonth) {
                $result[(int) $payment->ind] = round($accumulatedByMonth[$paymentMonth] * -1, 2);

                continue;
            }

            if ($paymentMonth < $startMonth) {
                $result[(int) $payment->ind] = 0.0;

                continue;
            }

            $referenceMonth = Carbon::parse($firstPaymentDateByMonth[$paymentMonth])->format('Y-m');
            $monthlyFee = $this->resolveMonthlyFee($allFees, $referenceMonth);
            $nominalTotal = $monthlyFee * (1 + $surchargeMultiplier);
            $remainingDebt = $nominalTotal - $accumulatedByMonth[$paymentMonth];

            $result[(int) $payment->ind] = round(max(0.0, $remainingDebt), 2);
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

    private function resolveMonthlyFee(Collection $fees, string $referenceMonth): float
    {
        $fee = $fees
            ->filter(fn (Fee $fee, string $month): bool => $month <= $referenceMonth)
            ->last();

        return $fee ? round((float) $fee->total, 2) : 0.0;
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
