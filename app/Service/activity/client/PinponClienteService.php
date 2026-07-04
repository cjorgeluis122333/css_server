<?php

namespace App\Service\activity\client;

use App\Models\activities\client\PinponCliente;
use Illuminate\Database\Eloquent\Collection;

class PinponClienteService
{
    public function getAll(): Collection
    {
        return PinponCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
