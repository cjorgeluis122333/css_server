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
            ->where('mes', '<=', $mes)
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
     * Calcula la deuda acumulada del socio al momento de cada pago registrado.
     *
     * @return array<int, float> Mapa de ind -> deuda
     */
    public function computeRunningDebtMap(int $acc): array
    {
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
                } catch (Exception) {
                    // Mantener el inicio por defecto cuando la fecha historica no es parseable.
                }
            }

            $childrenData = $this->getAdultChildrenData($partner);
            $surchargeMultiplier = $childrenData['multiplier'] ?? 0.0;
        }

        $payments = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'asc')
            ->orderBy('ind', 'asc')
            ->get(['ind', 'fecha', 'mes', 'monto']);

        if ($payments->isEmpty()) {
            return [];
        }

        $maxMesPaid = $payments->max('mes') ?? now()->format('Y-m');
        $maxPaymentDateMonth = $payments
            ->map(fn (HistoryPay $payment): string => Carbon::parse(
                $this->normalizeLedgerDate($payment->fecha, $payment->mes)
            )->format('Y-m'))
            ->max();
        $endMonth = max($maxMesPaid, $maxPaymentDateMonth, now()->format('Y-m'));

        $allFees = Fee::query()
            ->select(['mes', 'cuota', 'impuesto'])
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $firstPaymentDateByMonth = [];

        foreach ($payments as $payment) {
            if (! isset($firstPaymentDateByMonth[$payment->mes])) {
                $firstPaymentDateByMonth[$payment->mes] = $this->normalizeLedgerDate($payment->fecha, $payment->mes);
            }
        }

        $ledgerEntries = [];

        foreach ($this->generateMonthRange($startMonth, $endMonth) as $month) {
            $referenceMonth = isset($firstPaymentDateByMonth[$month])
                ? Carbon::parse($firstPaymentDateByMonth[$month])->format('Y-m')
                : $month;

            $ledgerEntries[] = [
                'date' => "{$month}-01",
                'type' => 'charge',
                'priority' => 0,
                'ind' => 0,
                'amount' => $this->resolveMonthlyFee($allFees, $referenceMonth) * (1 + $surchargeMultiplier),
            ];
        }

        foreach ($payments as $payment) {
            $ledgerEntries[] = [
                'date' => $this->normalizeLedgerDate($payment->fecha, $payment->mes),
                'type' => 'payment',
                'priority' => 1,
                'ind' => (int) $payment->ind,
                'amount' => (float) $payment->monto,
            ];
        }

        usort($ledgerEntries, function (array $left, array $right): int {
            return [$left['date'], $left['priority'], $left['ind']]
                <=> [$right['date'], $right['priority'], $right['ind']];
        });

        $result = [];
        $runningDebt = 0.0;

        foreach ($ledgerEntries as $entry) {
            if ($entry['type'] === 'charge') {
                $runningDebt += (float) $entry['amount'];

                continue;
            }

            $runningDebt -= (float) $entry['amount'];
            $result[(int) $entry['ind']] = round($runningDebt, 2);
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
}
