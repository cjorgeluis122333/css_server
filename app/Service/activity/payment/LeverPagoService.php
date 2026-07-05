<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\LeverPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeverPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return LeverPago::query()
            ->leftJoin('0cc_lever_clientes', '0cc_lever_pagos_unificado.cedula', '=', '0cc_lever_clientes.cedula')
            ->select('0cc_lever_pagos_unificado.*', '0cc_lever_clientes.nombre')
            ->orderBy('0cc_lever_pagos_unificado.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (LeverPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return LeverPago::query()
            ->leftJoin('0cc_lever_clientes', '0cc_lever_pagos_unificado.cedula', '=', '0cc_lever_clientes.cedula')
            ->select('0cc_lever_pagos_unificado.*', '0cc_lever_clientes.nombre')
            ->where('0cc_lever_pagos_unificado.mes', $mes)
            ->orderBy('0cc_lever_pagos_unificado.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (LeverPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): LeverPago
    {
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => LeverPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(LeverPago $pago): LeverPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
