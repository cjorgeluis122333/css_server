<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\KaratePago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KaratePagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return KaratePago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (KaratePago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return KaratePago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (KaratePago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): KaratePago
    {
        return DB::transaction(fn () => KaratePago::create($data));
    }

    private function formatFecha(KaratePago $pago): KaratePago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}

