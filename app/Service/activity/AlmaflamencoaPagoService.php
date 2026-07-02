<?php

namespace App\Service\activity;

use App\Models\activities\AlmaflamencoaPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AlmaflamencoaPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return AlmaflamencoaPago::query()
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return AlmaflamencoaPago::query()
            ->where('mes', $mes)
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): AlmaflamencoaPago
    {
        return DB::transaction(fn () => AlmaflamencoaPago::create($data));
    }
}
