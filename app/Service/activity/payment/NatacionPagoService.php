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
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (NatacionPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return NatacionPago::query()
            ->where('mes', $mes)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (NatacionPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): NatacionPago
    {
        return DB::transaction(fn () => NatacionPago::create($data));
    }

    private function formatFecha(NatacionPago $pago): NatacionPago
    {
        if ($pago->fecha) {
            $pago->fecha = Carbon::createFromTimestamp($pago->fecha)->format('d-m-Y');
        }

        return $pago;
    }
}
