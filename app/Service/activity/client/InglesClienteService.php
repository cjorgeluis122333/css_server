<?php

namespace App\Service\activity\client;

use App\Models\activities\client\InglesCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InglesClienteService
{
    public function getAll(): Collection
    {
        return InglesCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): InglesCliente
    {
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => InglesCliente::create($data));
    }

    public function findByCedula(string $cedula): ?InglesCliente
    {
        return InglesCliente::query()->where('cedula', $cedula)->first();
    }
}
