<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\InglesPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InglesPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return InglesPago::query()
            ->leftJoin('0cc_ingles_clientes', '0cc_ingles_pagos_unificado.cedula', '=', '0cc_ingles_clientes.cedula')
            ->select('0cc_ingles_pagos_unificado.*', '0cc_ingles_clientes.nombre')
            ->orderBy('0cc_ingles_pagos_unificado.ano_tabla', 'desc')
            ->orderBy('0cc_ingles_pagos_unificado.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (InglesPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return InglesPago::query()
            ->leftJoin('0cc_ingles_clientes', '0cc_ingles_pagos_unificado.cedula', '=', '0cc_ingles_clientes.cedula')
            ->select('0cc_ingles_pagos_unificado.*', '0cc_ingles_clientes.nombre')
            ->where('0cc_ingles_pagos_unificado.mes', $mes)
            ->orderBy('0cc_ingles_pagos_unificado.ano_tabla', 'desc')
            ->orderBy('0cc_ingles_pagos_unificado.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (InglesPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMonthYear(int $year, int $month): array
    {
        $mesBase = "{$year}-{$month}";
        $mesPadded = "{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $mesValues = array_unique([$mesBase, $mesPadded]);

        $registros = InglesPago::query()
            ->leftJoin('0cc_ingles_clientes', '0cc_ingles_pagos_unificado.cedula', '=', '0cc_ingles_clientes.cedula')
            ->select('0cc_ingles_pagos_unificado.*', '0cc_ingles_clientes.nombre')
            ->whereIn('0cc_ingles_pagos_unificado.mes', $mesValues)
            ->orderBy('0cc_ingles_pagos_unificado.ano_tabla', 'desc')
            ->orderBy('0cc_ingles_pagos_unificado.mes', 'desc')
            ->get()
            ->map(fn (InglesPago $pago) => $this->formatFecha($pago));

        $totalMeses = InglesPago::query()
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

        return InglesPago::query()
            ->leftJoin('0cc_ingles_clientes', '0cc_ingles_pagos_unificado.cedula', '=', '0cc_ingles_clientes.cedula')
            ->select('0cc_ingles_pagos_unificado.*', '0cc_ingles_clientes.nombre')
            ->whereBetween('0cc_ingles_pagos_unificado.fecha', [$startTimestamp, $endTimestamp])
            ->orderBy('0cc_ingles_pagos_unificado.fecha', 'desc')
            ->get()
            ->map(fn (InglesPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): InglesPago
    {
        $data['ano_tabla'] = (int) substr($data['mes'], 0, 4);

        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => InglesPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(InglesPago $pago): InglesPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
