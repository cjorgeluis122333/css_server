<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\LeverPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeverPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return LeverPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (LeverPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return LeverPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (LeverPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): LeverPago
    {
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => LeverPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(LeverPago $pago): LeverPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}

