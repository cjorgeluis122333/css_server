<?php

namespace App\Service\activity\client;

use App\Models\activities\client\BasquetCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BasquetClienteService
{
    public function getAll(): Collection
    {
        return BasquetCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): BasquetCliente
    {
        return DB::transaction(fn () => BasquetCliente::create($data));
    }

    public function findByCedula(string $cedula): ?BasquetCliente
    {
        return BasquetCliente::query()->where('cedula', $cedula)->first();
    }
}
