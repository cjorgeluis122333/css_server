<?php

namespace App\Service\activity\client;

use App\Models\activities\client\OnboxCliente;
use Illuminate\Database\Eloquent\Collection;

class OnboxClienteService
{
    public function getAll(): Collection
    {
        return OnboxCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
