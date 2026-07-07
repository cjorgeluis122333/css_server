<?php

namespace App\Service\activity\client;

use App\Models\activities\client\AlmaflamencaCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AlmaflamencaClienteService
{
    public function getAll(): Collection
    {
        return AlmaflamencaCliente::query()
            ->orderBy('last_pay_mont', 'desc')
            ->get();
    }

    public function create(array $data): AlmaflamencaCliente
    {
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => AlmaflamencaCliente::create($data));
    }

    public function findByCedula(string $cedula): ?AlmaflamencaCliente
    {
        return AlmaflamencaCliente::query()->where('cedula', $cedula)->first();
    }
}
