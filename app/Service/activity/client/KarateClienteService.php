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
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => KarateCliente::create($data));
    }

    public function findByCedula(string $cedula): ?KarateCliente
    {
        return KarateCliente::query()->where('cedula', $cedula)->first();
    }
}
