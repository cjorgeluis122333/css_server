<?php

namespace App\Service;

use App\Models\Fee;
use Illuminate\Database\Eloquent\Collection;

class FeeService
{
    public function getAll(): Collection
    {
        return Fee::orderBy('mes', 'desc')->get();
    }

    public function getByMonth(string $mes): ?Fee
    {
        return Fee::where('mes', $mes)->first();
    }

    public function store(array $data): Fee
    {
        return Fee::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $Fee = Fee::findOrFail($id);
        return $Fee->update($data);
    }

    public function delete(int $id): bool
    {
        $Fee = Fee::findOrFail($id);
        return $Fee->delete();
    }
}
