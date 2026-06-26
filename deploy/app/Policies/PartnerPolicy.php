<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\partners\Partner;
use App\Models\User;

class PartnerPolicy
{
    public function view(User $user, Partner $partner): bool
    {
        if ($user->hasRole(UserRole::ADMIN, UserRole::OPERATOR)) {
            return true;
        }

        // PARTNER y HONORARY solo ven sus propios datos
        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY)) {
            return $user->acc === $partner->acc;
        }

        return false;
    }

    public function viewDebts(User $user, Partner $partner): bool
    {
        if ($user->hasRole(UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR)) {
            return true;
        }

        // PARTNER solo puede ver su propia deuda
        if ($user->hasRole(UserRole::PARTNER)) {
            return $user->acc === $partner->acc;
        }

        return false;
    }

    public function update(User $user, Partner $partner): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::OPERATOR);
    }

    public function delete(User $user, Partner $partner): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
