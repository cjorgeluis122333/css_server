<?php

namespace App\Service\activity;

use App\Models\activities\NatacionPago;
use Illuminate\Pagination\LengthAwarePaginator;

class NatacionPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return NatacionPago::query()->paginate($perPage);
    }
}
