<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\KaratePago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KaratePagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return KaratePago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return KaratePago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): KaratePago
    {
        return DB::transaction(fn () => KaratePago::create($data));
    }
}
