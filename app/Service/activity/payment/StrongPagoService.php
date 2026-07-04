<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\StrongPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StrongPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return StrongPago::query()
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (StrongPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return StrongPago::query()
            ->where('mes', $mes)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (StrongPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): StrongPago
    {
        $data['ano'] = (int) substr($data['mes'], 0, 4);

        if (empty($data['fecha'])) {
            $data['fecha'] = time();
        }

        $pago = DB::transaction(fn () => StrongPago::create($data));

        return $this->formatFecha($pago);
    }

    private function formatFecha(StrongPago $pago): StrongPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}
