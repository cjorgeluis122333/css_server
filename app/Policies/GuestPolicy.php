<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Guest;
use App\Models\User;

class GuestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR,
            UserRole::HONORARY, UserRole::PARTNER
        );
    }

    public function view(User $user, Guest $guest): bool
    {
        if ($user->hasRole(UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR)) {
            return true;
        }

        // PARTNER y HONORARY solo ven invitados de su propia acción
        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY)) {
            return $user->acc === $guest->acc;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR,
            UserRole::HONORARY, UserRole::PARTNER
        );
    }

    public function update(User $user, Guest $guest): bool
    {
        if ($user->hasRole(UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR)) {
            return true;
        }

        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY)) {
            return $user->acc === $guest->acc;
        }

        return false;
    }

    public function delete(User $user, Guest $guest): bool
    {
        if ($user->hasRole(UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR)) {
            return true;
        }

        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY)) {
            return $user->acc === $guest->acc;
        }

        return false;
    }
}
