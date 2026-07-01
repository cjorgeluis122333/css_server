<?php

namespace App\Service\activity;

use App\Models\activities\StrongPago;
use Illuminate\Pagination\LengthAwarePaginator;

class StrongPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return StrongPago::query()->paginate($perPage);
    }
}
