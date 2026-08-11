<?php

namespace App\Service\activity\client;

use App\Models\activities\client\BattingCliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BattingClienteService
{
    public function getAll(): Collection
    {
        return BattingCliente::query()
            ->orderBy('last_pay_mont', 'desc')
            ->get();
    }

    public function create(array $data): BattingCliente
    {
        $data['padres'] = $data['padres'] ?? '[{"cedula":"","nombre":""},{"cedula":"","nombre":""},{"cedula":"","nombre":""}]';

        return DB::transaction(fn () => BattingCliente::create($data));
    }

    public function findByCedula(string $cedula): ?BattingCliente
    {
        return BattingCliente::query()->where('cedula', $cedula)->first();
    }
}
