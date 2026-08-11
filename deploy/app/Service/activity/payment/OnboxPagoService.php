<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\OnboxPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OnboxPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return OnboxPago::query()
            ->leftJoin('0cc_onbox_clientes', '0cc_onbox_pagos_all.cedula', '=', '0cc_onbox_clientes.cedula')
            ->select('0cc_onbox_pagos_all.*', '0cc_onbox_clientes.nombre')
            ->orderBy('0cc_onbox_pagos_all.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (OnboxPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return OnboxPago::query()
            ->leftJoin('0cc_onbox_clientes', '0cc_onbox_pagos_all.cedula', '=', '0cc_onbox_clientes.cedula')
            ->select('0cc_onbox_pagos_all.*', '0cc_onbox_clientes.nombre')
            ->where('0cc_onbox_pagos_all.mes', $mes)
            ->orderBy('0cc_onbox_pagos_all.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (OnboxPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMonthYear(int $year, int $month): array
    {
        $mesBase = "{$year}-{$month}";
        $mesPadded = "{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $mesValues = array_unique([$mesBase, $mesPadded]);

        $registros = OnboxPago::query()
            ->leftJoin('0cc_onbox_clientes', '0cc_onbox_pagos_all.cedula', '=', '0cc_onbox_clientes.cedula')
            ->select('0cc_onbox_pagos_all.*', '0cc_onbox_clientes.nombre')
            ->whereIn('0cc_onbox_pagos_all.mes', $mesValues)
            ->orderBy('0cc_onbox_pagos_all.mes', 'desc')
            ->get()
            ->map(fn (OnboxPago $pago) => $this->formatFecha($pago));

        $totalMeses = OnboxPago::query()
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

        return OnboxPago::query()
            ->leftJoin('0cc_onbox_clientes', '0cc_onbox_pagos_all.cedula', '=', '0cc_onbox_clientes.cedula')
            ->select('0cc_onbox_pagos_all.*', '0cc_onbox_clientes.nombre')
            ->whereBetween('0cc_onbox_pagos_all.fecha', [$startTimestamp, $endTimestamp])
            ->orderBy('0cc_onbox_pagos_all.fecha', 'desc')
            ->get()
            ->map(fn (OnboxPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): OnboxPago
    {
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => OnboxPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(OnboxPago $pago): OnboxPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
