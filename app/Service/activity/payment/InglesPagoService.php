<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\InglesPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
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
