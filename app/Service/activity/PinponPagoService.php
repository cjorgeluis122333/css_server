<?php

namespace App\Service\activity;

use App\Models\activities\PinponPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PinponPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return PinponPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return PinponPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): PinponPago
    {
        return DB::transaction(fn () => PinponPago::create($data));
    }
}
