<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\OnboxPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OnboxPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return OnboxPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return OnboxPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): OnboxPago
    {
        return DB::transaction(fn () => OnboxPago::create($data));
    }
}
