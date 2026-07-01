<?php

namespace App\Service\activity;

use App\Models\activities\LeverPago;
use Illuminate\Pagination\LengthAwarePaginator;

class LeverPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return LeverPago::query()->paginate($perPage);
    }
}
