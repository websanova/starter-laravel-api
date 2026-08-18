<?php

namespace App\Services\Stripe;

use App\Contracts\ChangeSubscriptionPlanProvider;
use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use Laravel\Cashier\Subscription;

class ChangeSubscriptionPlan implements ChangeSubscriptionPlanProvider
{
    /**
     * Swap to a different plan.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): Subscription
    {
        $user->subscription()->swap($plan->priceId($interval));

        $user->fillPlan()->save();

        return $user->subscription();
    }
}
