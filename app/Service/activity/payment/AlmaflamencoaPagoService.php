<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\AlmaflamencoaPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
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
