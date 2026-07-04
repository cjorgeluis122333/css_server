<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\BattingPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BattingPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return BattingPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (BattingPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return BattingPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (BattingPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): BattingPago
    {
        return DB::transaction(fn () => BattingPago::create($data));
    }

    private function formatFecha(BattingPago $pago): BattingPago
    {
        if ($pago->fecha) {
            $pago->fecha = Carbon::createFromTimestamp($pago->fecha)->format('d-m-Y');
        }

        return $pago;
    }
}

