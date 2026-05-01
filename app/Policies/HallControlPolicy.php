<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\HallControl;
use App\Models\User;

class HallControlPolicy
{
    public function update(User $user, HallControl $hallControl): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        // PARTNER y HONORARY solo pueden editar registros de su propia acción
        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY)) {
            return $user->acc === (int) $hallControl->acc;
        }

        return false;
    }

    public function delete(User $user, HallControl $hallControl): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY)) {
            return $user->acc === (int) $hallControl->acc;
        }

        return false;
    }
}
