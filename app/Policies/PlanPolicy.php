<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

class PlanPolicy
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
        return $user->hasPermissionTo('plans.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plan $plan): bool
    {
        return $user->hasPermissionTo('plans.manage');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('plans.manage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plan $plan): bool
    {
        return $user->hasPermissionTo('plans.manage');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return $user->hasPermissionTo('plans.manage')
            && $plan->users()->count() === 0;
    }
}
