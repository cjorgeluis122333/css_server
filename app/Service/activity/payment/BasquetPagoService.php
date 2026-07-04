<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\BasquetPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BasquetPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return BasquetPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return BasquetPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): BasquetPago
    {
        return DB::transaction(fn () => BasquetPago::create($data));
    }
}
