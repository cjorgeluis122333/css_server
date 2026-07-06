<?php

namespace App\Service\activity\client;

use App\Models\activities\client\StrongCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StrongClienteService
{
    public function getAll(): Collection
    {
        return StrongCliente::query()
            ->orderBy('cedula')
            ->get();
    }

    public function create(array $data): StrongCliente
    {
        return DB::transaction(fn () => StrongCliente::create($data));
    }

    public function findByCedula(string $cedula): ?StrongCliente
    {
        return StrongCliente::query()->where('cedula', $cedula)->first();
    }
}
