<?php

namespace App\Service\activity\client;

use App\Models\activities\client\VoleibolCliente;
use Illuminate\Database\Eloquent\Collection;

class VoleibolClienteService
{
    public function getAll(): Collection
    {
        return VoleibolCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
