<?php

namespace App\Service\activity;

use App\Models\activities\PinponPago;
use Illuminate\Pagination\LengthAwarePaginator;

class PinponPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return PinponPago::query()->paginate($perPage);
    }
}
