<?php

namespace App\Service;

use App\Models\Hall;
use Illuminate\Database\Eloquent\Collection;
class HallService
{
    public function getAll(): Collection
    {
        return Hall::all();
    }

    public function getById(int $id): ?Hall
    {
        return Hall::find($id);
    }

    public function create(array $data): Hall
    {
        return Hall::create($data);
    }

    public function update(Hall $Hall, array $data): Hall
    {
        $Hall->update($data);
        return $Hall;
    }

    public function delete(Hall $Hall): bool
    {
        return $Hall->delete();
    }
}
