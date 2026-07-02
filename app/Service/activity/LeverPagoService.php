<?php

namespace App\Service\activity;

use App\Models\activities\LeverPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeverPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return LeverPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return LeverPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): LeverPago
    {
        return DB::transaction(fn () => LeverPago::create($data));
    }
}
