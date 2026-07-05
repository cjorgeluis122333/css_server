<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\KaratePago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KaratePagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return KaratePago::query()
            ->leftJoin('0cc_karate_clientes', '0cc_karate_pagos.cedula', '=', '0cc_karate_clientes.cedula')
            ->select('0cc_karate_pagos.*', '0cc_karate_clientes.nombre')
            ->orderBy('0cc_karate_pagos.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (KaratePago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return KaratePago::query()
            ->leftJoin('0cc_karate_clientes', '0cc_karate_pagos.cedula', '=', '0cc_karate_clientes.cedula')
            ->select('0cc_karate_pagos.*', '0cc_karate_clientes.nombre')
            ->where('0cc_karate_pagos.mes', $mes)
            ->orderBy('0cc_karate_pagos.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (KaratePago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): KaratePago
    {
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => KaratePago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(KaratePago $pago): KaratePago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
