<?php

namespace App\Service\activity\client;

use App\Models\activities\client\BasquetCliente;
use Illuminate\Database\Eloquent\Collection;

class BasquetClienteService
{
    public function getAll(): Collection
    {
        return BasquetCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
