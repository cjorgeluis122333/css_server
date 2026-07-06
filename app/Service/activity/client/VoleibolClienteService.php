<?php

namespace App\Service\activity\client;

use App\Models\activities\client\VoleibolCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VoleibolClienteService
{
    public function getAll(): Collection
    {
        return VoleibolCliente::query()
            ->orderBy('ind')
            ->get();
    }

    public function create(array $data): VoleibolCliente
    {
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => VoleibolCliente::create($data));
    }

    public function findByCedula(string $cedula): ?VoleibolCliente
    {
        return VoleibolCliente::query()->where('cedula', $cedula)->first();
    }
}
