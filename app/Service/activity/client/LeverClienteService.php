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
            ->orderBy('last_pay_mont', 'desc')
            ->get();
    }

    public function create(array $data): LeverCliente
    {
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => LeverCliente::create($data));
    }

    public function findByCedula(string $cedula): ?LeverCliente
    {
        return LeverCliente::query()->where('cedula', $cedula)->first();
    }
}
