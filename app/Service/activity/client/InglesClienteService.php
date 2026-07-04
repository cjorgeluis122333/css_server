<?php

namespace App\Service\activity\client;

use App\Models\activities\client\InglesCliente;
use Illuminate\Database\Eloquent\Collection;

class InglesClienteService
{
    public function getAll(): Collection
    {
        return InglesCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
