<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\partners\HistoryPay;
use App\Models\User;

class HistoryPayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function update(User $user, HistoryPay $historyPay): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function delete(User $user, HistoryPay $historyPay): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
