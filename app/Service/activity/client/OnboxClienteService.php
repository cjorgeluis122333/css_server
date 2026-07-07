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
            ->orderBy('last_pay_mont', 'desc')
            ->get();
    }

    public function create(array $data): OnboxCliente
    {
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => OnboxCliente::create($data));
    }

    public function findByCedula(string $cedula): ?OnboxCliente
    {
        return OnboxCliente::query()->where('cedula', $cedula)->first();
    }
}
