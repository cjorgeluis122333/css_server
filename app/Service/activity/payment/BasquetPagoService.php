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
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (BasquetPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return BasquetPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (BasquetPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): BasquetPago
    {
        return DB::transaction(fn () => BasquetPago::create($data));
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

