<?php

namespace App\Service\activity\client;

use App\Models\activities\client\LeverCliente;
use Illuminate\Database\Eloquent\Collection;

class LeverClienteService
{
    public function getAll(): Collection
    {
        return LeverCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
