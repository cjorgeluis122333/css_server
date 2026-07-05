<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\BasquetPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BasquetPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return BasquetPago::query()
            ->leftJoin('0cc_basquet_clientes', '0cc_basquet_pagos.cedula', '=', '0cc_basquet_clientes.cedula')
            ->select('0cc_basquet_pagos.*', '0cc_basquet_clientes.nombre')
            ->orderBy('0cc_basquet_pagos.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (BasquetPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return BasquetPago::query()
            ->leftJoin('0cc_basquet_clientes', '0cc_basquet_pagos.cedula', '=', '0cc_basquet_clientes.cedula')
            ->select('0cc_basquet_pagos.*', '0cc_basquet_clientes.nombre')
            ->where('0cc_basquet_pagos.mes', $mes)
            ->orderBy('0cc_basquet_pagos.mes', 'desc')
            ->paginate($perPage)
            ->through(fn (BasquetPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): BasquetPago
    {
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => BasquetPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(BasquetPago $pago): BasquetPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
