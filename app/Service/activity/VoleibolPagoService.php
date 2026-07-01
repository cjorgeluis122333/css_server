<?php

namespace App\Service\activity;

use App\Models\activities\VoleibolPago;
use Illuminate\Pagination\LengthAwarePaginator;

class VoleibolPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return VoleibolPago::query()->paginate($perPage);
    }
}
