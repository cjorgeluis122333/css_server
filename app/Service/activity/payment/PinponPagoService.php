<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\PinponPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PinponPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return PinponPago::query()
            ->leftJoin('0cc_pinpon_clientes', '0cc_pinpon_pagos_unificada.cedula', '=', '0cc_pinpon_clientes.cedula')
            ->select('0cc_pinpon_pagos_unificada.*', '0cc_pinpon_clientes.nombre')
            ->orderBy('0cc_pinpon_pagos_unificada.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (PinponPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return PinponPago::query()
            ->leftJoin('0cc_pinpon_clientes', '0cc_pinpon_pagos_unificada.cedula', '=', '0cc_pinpon_clientes.cedula')
            ->select('0cc_pinpon_pagos_unificada.*', '0cc_pinpon_clientes.nombre')
            ->where('0cc_pinpon_pagos_unificada.mes', $mes)
            ->orderBy('0cc_pinpon_pagos_unificada.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (PinponPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): PinponPago
    {
        $data['anio_origen'] = (int) substr($data['mes'], 0, 4);

        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => PinponPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(PinponPago $pago): PinponPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
