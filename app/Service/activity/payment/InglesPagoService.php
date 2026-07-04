<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\InglesPago;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InglesPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return InglesPago::query()
            ->orderBy('ano_tabla', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (InglesPago $pago) => $this->formatFecha($pago));
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return InglesPago::query()
            ->where('mes', $mes)
            ->orderBy('ano_tabla', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage)
            ->through(fn (InglesPago $pago) => $this->formatFecha($pago));
    }

    public function create(array $data): InglesPago
    {
        return DB::transaction(fn () => InglesPago::create($data));
    }

    private function formatFecha(InglesPago $pago): InglesPago
    {
        if ($pago->fecha) {
            $originalFecha = $pago->fecha;
            $pago->mergeCasts(['fecha' => 'string']);
            $pago->fecha = Carbon::createFromTimestamp($originalFecha)->format('d-m-Y');
        }

        return $pago;
    }
}

