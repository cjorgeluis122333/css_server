<?php

namespace App\Service\activity;

use App\Models\activities\OnboxPago;
use Illuminate\Pagination\LengthAwarePaginator;

class OnboxPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return OnboxPago::query()->paginate($perPage);
    }
}
