<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
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
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.manage')
            && !$target->hasRole(UserRole::Super);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.manage')
            && !$target->hasRole(UserRole::Super);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.manage')
            && !$target->hasRole(UserRole::Super)
            && !$target->hasRole(UserRole::Admin);
    }

    /**
     * Determine whether the user can assign a role to the model.
     */
    public function assignRole(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.assign-role')
            && !$target->hasRole(UserRole::Super)
            && !$target->hasRole(UserRole::Admin);
    }

    /**
     * Determine whether the user can remove a role from the model.
     */
    public function removeRole(User $user, User $target): bool
    {
        return false;
    }
}
