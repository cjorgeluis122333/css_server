<?php

namespace App\Service\activity\client;

use App\Models\activities\client\NatacionCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class NatacionClienteService
{
    public function getAll(): Collection
    {
        return NatacionCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): NatacionCliente
    {
        return DB::transaction(fn () => NatacionCliente::create($data));
    }

    public function findByCedula(string $cedula): ?NatacionCliente
    {
        return NatacionCliente::query()->where('cedula', $cedula)->first();
    }
}
