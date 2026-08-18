<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;

interface ChangeSubscriptionPlanProvider
{
    /**
     * Swap the subscription to a different plan or interval.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): mixed;
}
