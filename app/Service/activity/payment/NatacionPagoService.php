<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\NatacionPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NatacionPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return NatacionPago::query()
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return NatacionPago::query()
            ->where('mes', $mes)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): NatacionPago
    {
        return DB::transaction(fn () => NatacionPago::create($data));
    }
}
