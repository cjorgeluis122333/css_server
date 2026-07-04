<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\StrongPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StrongPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return StrongPago::query()
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return StrongPago::query()
            ->where('mes', $mes)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): StrongPago
    {
        return DB::transaction(fn () => StrongPago::create($data));
    }
}
