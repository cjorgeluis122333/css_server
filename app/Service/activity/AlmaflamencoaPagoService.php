<?php

namespace App\Service\activity;

use App\Models\activities\AlmaflamencoaPago;
use Illuminate\Pagination\LengthAwarePaginator;

class AlmaflamencoaPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return AlmaflamencoaPago::query()->paginate($perPage);
    }
}
