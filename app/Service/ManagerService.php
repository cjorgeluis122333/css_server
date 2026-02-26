<?php

namespace App\Service;
use App\Models\Manager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
class ManagerService
{
    /**
     * Get all managers without pagination.
     */
    public function getAllManagers(): Collection
    {
        return Manager::all();
    }

    /**
     * Create a new manager record.
     */
    public function createManager(array $data): Manager
    {
        return DB::transaction(function () use ($data) {
            return Manager::create($data);
        });
    }

    /**
     * Update an existing manager.
     */
    public function updateManager(Manager $manager, array $data): Manager
    {
        return DB::transaction(function () use ($manager, $data) {
            // update() es más directo si ya tenemos los datos validados
            $manager->update($data);
            return $manager;
        });
    }

    /**
     * Remove a manager from the database.
     */
    public function deleteManager(Manager $manager): bool
    {
        return DB::transaction(function () use ($manager) {
            return (bool) $manager->delete();
        });
    }
}
