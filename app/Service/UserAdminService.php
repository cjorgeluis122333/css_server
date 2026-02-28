<?php

namespace App\Service;

use App\Models\Manager;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserAdminService
{
    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create($data);
        });
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): Manager
    {
        return DB::transaction(function () use ($user, $data) {
            // update() es más directo si ya tenemos los datos validados
            $user->update($data);
            return $user;
        });
    }


    /**
     * Remove a user from the database.
     */
    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            return (bool) $user->delete();
        });
    }

}
