<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\StrongPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StrongPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return StrongPago::query()
            ->leftJoin('0cc_strong_clientes', '0cc_strong_pagos_unificada.cedula', '=', '0cc_strong_clientes.cedula')
            ->select('0cc_strong_pagos_unificada.*', '0cc_strong_clientes.nombre')
            ->orderBy('0cc_strong_pagos_unificada.ano', 'desc')
            ->orderBy('0cc_strong_pagos_unificada.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (StrongPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return StrongPago::query()
            ->leftJoin('0cc_strong_clientes', '0cc_strong_pagos_unificada.cedula', '=', '0cc_strong_clientes.cedula')
            ->select('0cc_strong_pagos_unificada.*', '0cc_strong_clientes.nombre')
            ->where('0cc_strong_pagos_unificada.mes', $mes)
            ->orderBy('0cc_strong_pagos_unificada.ano', 'desc')
            ->orderBy('0cc_strong_pagos_unificada.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (StrongPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMonthYear(int $year, int $month): array
    {
        $mesBase = "{$year}-{$month}";
        $mesPadded = "{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $mesValues = array_unique([$mesBase, $mesPadded]);

        $registros = StrongPago::query()
            ->leftJoin('0cc_strong_clientes', '0cc_strong_pagos_unificada.cedula', '=', '0cc_strong_clientes.cedula')
            ->select('0cc_strong_pagos_unificada.*', '0cc_strong_clientes.nombre')
            ->whereIn('0cc_strong_pagos_unificada.mes', $mesValues)
            ->orderBy('0cc_strong_pagos_unificada.ano', 'desc')
            ->orderBy('0cc_strong_pagos_unificada.mes', 'desc')
            ->get()
            ->map(fn (StrongPago $pago) => $this->formatFecha($pago));

        $totalMeses = StrongPago::query()
            ->selectRaw('COUNT(DISTINCT mes) as total')
            ->value('total');

        return [
            'registros' => $registros,
            'total_meses' => (int) $totalMeses,
        ];
    }

    public function filterByWeek(int $year, int $week): Collection
    {
        $startTimestamp = Carbon::now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY)->startOfDay()->timestamp;
        $endTimestamp = Carbon::now()->setISODate($year, $week)->endOfWeek(Carbon::SUNDAY)->endOfDay()->timestamp;

        return StrongPago::query()
            ->leftJoin('0cc_strong_clientes', '0cc_strong_pagos_unificada.cedula', '=', '0cc_strong_clientes.cedula')
            ->select('0cc_strong_pagos_unificada.*', '0cc_strong_clientes.nombre')
            ->whereBetween('0cc_strong_pagos_unificada.fecha', [$startTimestamp, $endTimestamp])
            ->orderBy('0cc_strong_pagos_unificada.fecha', 'desc')
            ->get()
            ->map(fn (StrongPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): StrongPago
    {
        $data['ano'] = (int) substr($data['mes'], 0, 4);

        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => StrongPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(StrongPago $pago): StrongPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
