<?php

namespace App\Service\activity\client;

use App\Models\activities\client\StrongCliente;
use Illuminate\Database\Eloquent\Collection;

class StrongClienteService
{
    public function getAll(): Collection
    {
        return StrongCliente::query()
            ->orderBy('cedula')
            ->get();
    }
}
