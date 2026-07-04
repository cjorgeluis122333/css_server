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
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (PinponPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return PinponPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (PinponPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): PinponPago
    {
        return DB::transaction(fn () => PinponPago::create($data));
    }

    private function formatFecha(PinponPago $pago): PinponPago
    {
        if ($pago->fecha) {
            $pago->fecha = Carbon::createFromTimestamp($pago->fecha)->format('d-m-Y');
        }

        return $pago;
    }
}

