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
        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => BattingPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(BattingPago $pago): BattingPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}

