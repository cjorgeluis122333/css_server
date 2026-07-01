<?php

namespace App\Service\activity;

use App\Models\activities\BasquetPago;
use Illuminate\Pagination\LengthAwarePaginator;

class BasquetPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return BasquetPago::query()->paginate($perPage);
    }
}
