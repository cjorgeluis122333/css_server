<?php

namespace App\Service\activity\client;

use App\Models\activities\client\KarateCliente;
use Illuminate\Database\Eloquent\Collection;

class KarateClienteService
{
    public function getAll(): Collection
    {
        return KarateCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
