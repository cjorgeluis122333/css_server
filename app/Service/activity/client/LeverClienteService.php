<?php

namespace App\Service\activity\client;

use App\Models\activities\client\LeverCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LeverClienteService
{
    public function getAll(): Collection
    {
        return LeverCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): LeverCliente
    {
        $data['padres'] = $data['padres'] ?? '';

        return DB::transaction(fn () => LeverCliente::create($data));
    }

    public function findByCedula(string $cedula): ?LeverCliente
    {
        return LeverCliente::query()->where('cedula', $cedula)->first();
    }
}
