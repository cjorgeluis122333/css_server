<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\NatacionPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NatacionPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return NatacionPago::query()
            ->leftJoin('0cc_natacion_clientes', '0cc_natacion_pagos.cedula', '=', '0cc_natacion_clientes.cedula')
            ->select('0cc_natacion_pagos.*', '0cc_natacion_clientes.nombre')
            ->orderBy('0cc_natacion_pagos.anio', 'desc')
            ->orderBy('0cc_natacion_pagos.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (NatacionPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return NatacionPago::query()
            ->leftJoin('0cc_natacion_clientes', '0cc_natacion_pagos.cedula', '=', '0cc_natacion_clientes.cedula')
            ->select('0cc_natacion_pagos.*', '0cc_natacion_clientes.nombre')
            ->where('0cc_natacion_pagos.mes', $mes)
            ->orderBy('0cc_natacion_pagos.anio', 'desc')
            ->orderBy('0cc_natacion_pagos.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (NatacionPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): NatacionPago
    {
        $data['anio'] = (int) substr($data['mes'], 0, 4);

        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => NatacionPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(NatacionPago $pago): NatacionPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
