<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\BattingPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BattingPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return BattingPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return BattingPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): BattingPago
    {
        return DB::transaction(fn () => BattingPago::create($data));
    }
}
