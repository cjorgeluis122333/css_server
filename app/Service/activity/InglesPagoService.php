<?php

namespace App\Service\activity;

use App\Models\activities\InglesPago;
use Illuminate\Pagination\LengthAwarePaginator;

class InglesPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return InglesPago::query()->paginate($perPage);
    }
}
