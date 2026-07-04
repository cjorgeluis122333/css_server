<?php

namespace App\Service\activity\client;

use App\Models\activities\client\KarateCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KarateClienteService
{
    public function getAll(): Collection
    {
        return KarateCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): KarateCliente
    {
        return DB::transaction(fn () => KarateCliente::create($data));
    }
}
