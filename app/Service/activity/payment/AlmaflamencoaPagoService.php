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
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (AlmaflamencoaPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return AlmaflamencoaPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (AlmaflamencoaPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): AlmaflamencoaPago
    {
        return DB::transaction(fn () => AlmaflamencoaPago::create($data));
    }

    private function formatFecha(AlmaflamencoaPago $pago): AlmaflamencoaPago
    {
        if ($pago->fecha) {
            $pago->fecha = Carbon::createFromTimestamp($pago->fecha)->format('d-m-Y');
        }

        return $pago;
    }
}

