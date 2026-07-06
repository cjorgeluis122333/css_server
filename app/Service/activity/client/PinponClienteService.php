<?php

namespace App\Service\activity\client;

use App\Models\activities\client\PinponCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PinponClienteService
{
    public function getAll(): Collection
    {
        return PinponCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): PinponCliente
    {
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => PinponCliente::create($data));
    }

    public function findByCedula(string $cedula): ?PinponCliente
    {
        return PinponCliente::query()->where('cedula', $cedula)->first();
    }
}
