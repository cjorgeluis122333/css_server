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
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => PinponPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(PinponPago $pago): PinponPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}

