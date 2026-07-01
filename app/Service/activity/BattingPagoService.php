<?php

namespace App\Service\activity;

use App\Models\activities\BattingPago;
use Illuminate\Pagination\LengthAwarePaginator;

class BattingPagoService
{
    public function paginated(int $perPage): LengthAwarePaginator
    {
        return BattingPago::query()->paginate($perPage);
    }
}
