<?php

namespace App\Service\activity;

use App\Models\activities\KaratePago;
use Illuminate\Pagination\LengthAwarePaginator;

class KaratePagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return KaratePago::query()->paginate($perPage);
    }
}
