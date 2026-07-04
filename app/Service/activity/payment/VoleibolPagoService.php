<?php

namespace App\Service\activity\payment;

use App\Models\activities\payment\VoleibolPago;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VoleibolPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return VoleibolPago::query()
            ->orderBy('ano_origen', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function filterByMes(string $mes, int $perPage): LengthAwarePaginator
    {
        return VoleibolPago::query()
            ->where('mes', $mes)
            ->orderBy('ano_origen', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): VoleibolPago
    {
        return DB::transaction(fn () => VoleibolPago::create($data));
    }
}
