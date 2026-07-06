<?php

namespace App\Service\activity\client;

use App\Models\activities\client\OnboxCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OnboxClienteService
{
    public function getAll(): Collection
    {
        return OnboxCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): OnboxCliente
    {
        return DB::transaction(fn () => OnboxCliente::create($data));
    }

    public function findByCedula(string $cedula): ?OnboxCliente
    {
        return OnboxCliente::query()->where('cedula', $cedula)->first();
    }
}
