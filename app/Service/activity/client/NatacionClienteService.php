<?php

namespace App\Service\activity\client;

use App\Models\activities\client\NatacionCliente;
use Illuminate\Database\Eloquent\Collection;

class NatacionClienteService
{
    public function getAll(): Collection
    {
        return NatacionCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
