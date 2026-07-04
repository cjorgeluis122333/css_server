<?php

namespace App\Service\activity\client;

use App\Models\activities\client\BattingCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BattingClienteService
{
    public function getAll(): Collection
    {
        return BattingCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): BattingCliente
    {
        return DB::transaction(fn () => BattingCliente::create($data));
    }
}
