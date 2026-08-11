<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\AlmaflamencoaPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlmaflamencoaPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return AlmaflamencoaPago::query()
            ->leftJoin('0cc_almaflamenca_clientes', '0cc_almaflamenca_pagos_unificada.cedula', '=', '0cc_almaflamenca_clientes.cedula')
            ->select('0cc_almaflamenca_pagos_unificada.*', '0cc_almaflamenca_clientes.nombre')
            ->orderBy('0cc_almaflamenca_pagos_unificada.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (AlmaflamencoaPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return AlmaflamencoaPago::query()
            ->leftJoin('0cc_almaflamenca_clientes', '0cc_almaflamenca_pagos_unificada.cedula', '=', '0cc_almaflamenca_clientes.cedula')
            ->select('0cc_almaflamenca_pagos_unificada.*', '0cc_almaflamenca_clientes.nombre')
            ->where('0cc_almaflamenca_pagos_unificada.mes', $mes)
            ->orderBy('0cc_almaflamenca_pagos_unificada.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (AlmaflamencoaPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMonthYear(int $year, int $month): array
    {
        $mesBase = "{$year}-{$month}";
        $mesPadded = "{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $mesValues = array_unique([$mesBase, $mesPadded]);

        $registros = AlmaflamencoaPago::query()
            ->leftJoin('0cc_almaflamenca_clientes', '0cc_almaflamenca_pagos_unificada.cedula', '=', '0cc_almaflamenca_clientes.cedula')
            ->select('0cc_almaflamenca_pagos_unificada.*', '0cc_almaflamenca_clientes.nombre')
            ->whereIn('0cc_almaflamenca_pagos_unificada.mes', $mesValues)
            ->orderBy('0cc_almaflamenca_pagos_unificada.mes', 'desc')
            ->get()
            ->map(fn (AlmaflamencoaPago $pago) => $this->formatFecha($pago));

        $totalMeses = AlmaflamencoaPago::query()
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

        return AlmaflamencoaPago::query()
            ->leftJoin('0cc_almaflamenca_clientes', '0cc_almaflamenca_pagos_unificada.cedula', '=', '0cc_almaflamenca_clientes.cedula')
            ->select('0cc_almaflamenca_pagos_unificada.*', '0cc_almaflamenca_clientes.nombre')
            ->whereBetween('0cc_almaflamenca_pagos_unificada.fecha', [$startTimestamp, $endTimestamp])
            ->orderBy('0cc_almaflamenca_pagos_unificada.fecha', 'desc')
            ->get()
            ->map(fn (AlmaflamencoaPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): AlmaflamencoaPago
    {
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => AlmaflamencoaPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(AlmaflamencoaPago $pago): AlmaflamencoaPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
