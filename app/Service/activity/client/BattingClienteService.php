<?php

namespace App\Service\activity\client;

use App\Models\activities\client\BattingCliente;
use Illuminate\Database\Eloquent\Collection;

class BattingClienteService
{
    public function getAll(): Collection
    {
        return BattingCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
