<?php

namespace App\Service;

use App\Models\HallControl;
use Illuminate\Database\Eloquent\Collection;

class HallControlService
{
    public function getAll(): Collection
    {
        // Puedes cambiar esto por paginación si la tabla crece mucho
        return HallControl::all();
    }

    public function getById(int $id): ?HallControl
    {
        return HallControl::find($id);
    }

    public function create(array $data): HallControl
    {
        return HallControl::create($data);
    }

    public function update(HallControl $registro, array $data): HallControl
    {
        $registro->update($data);
        return $registro;
    }

    public function delete(HallControl $registro): bool
    {
        return $registro->delete();
    }
}
