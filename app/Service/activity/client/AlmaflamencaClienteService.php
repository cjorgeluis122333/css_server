<?php

namespace App\Service\activity\client;

use App\Models\activities\client\AlmaflamencaCliente;
use Illuminate\Database\Eloquent\Collection;

class AlmaflamencaClienteService
{
    public function getAll(): Collection
    {
        return AlmaflamencaCliente::query()
            ->orderBy('ind')
            ->get();
    }
}
