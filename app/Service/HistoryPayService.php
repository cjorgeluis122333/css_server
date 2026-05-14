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
        // 1. Determinar el socio y su fecha de inicio unificada
        $partner = Partner::where('acc', $acc)
            ->where('categoria', PartnerCategory::TITULAR->value)
            ->first();

        $startMonth = '2019-01';
        $surchargeMultiplier = 0.0;

        if ($partner) {
            // Unificamos la lógica de fecha de ingreso con getAccountStatement
            $fechaIngreso = $partner->fecha_ingreso ?? $partner->fecha_ingreso_validada ?? $partner->ingreso;
            $fechaLimite = Carbon::create(2019, 1, 1);

            if ($fechaIngreso) {
                try {
                    $ingresoCarbon = Carbon::parse($fechaIngreso);
                    if ($ingresoCarbon->gte($fechaLimite)) {
                        $startMonth = $ingresoCarbon->format('Y-m');
                    }
                } catch (Exception) {
                    // Mantiene 2019-01 si falla el parseo
                }
            }

            // EXTRAEMOS EL MULTIPLICADOR DE HIJOS MAYORES
            $childrenData = $this->getAdultChildrenData($partner);
            $surchargeMultiplier = $childrenData['multiplier'] ?? 0.0;
        }

        // 2. Construir el lookup de cuota por mes incluyendo RECARGOS
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

            // APLICAMOS EL MULTIPLICADOR DE LA MISMA FORMA QUE EN getAccountStatement
            $nominalTotal = $currentFeeValue * (1 + $surchargeMultiplier);

            $cumulativeFee += $nominalTotal;
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

            // Mes base para acumular cuotas
            $fechaMonth = $payment->fecha
                ? Carbon::parse($payment->fecha)->format('Y-m')
                : $payment->mes;

            $totalFeesThrough = $cumFees[$fechaMonth] ?? 0.0;
            $result[(int) $payment->ind] = round($totalFeesThrough - $cumulativePaid, 2);
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
