<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class StatPolicy
{
    /**
     * Super users bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(UserRole::Super)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view stats.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('stats.view');
    }
}
