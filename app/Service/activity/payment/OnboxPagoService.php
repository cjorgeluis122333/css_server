<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\OnboxPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OnboxPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return OnboxPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (OnboxPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return OnboxPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (OnboxPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): OnboxPago
    {
        return DB::transaction(fn () => OnboxPago::create($data));
    }

    private function formatFecha(OnboxPago $pago): OnboxPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}

