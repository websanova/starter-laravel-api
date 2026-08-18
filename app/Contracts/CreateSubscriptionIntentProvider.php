<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;

interface CreateSubscriptionIntentProvider
{
    /**
     * Open a payment session and return the secret the client mounts its own
     * payment form against. The subscription is created up front and sits
     * incomplete until the client confirms, so this has to be safe to call
     * repeatedly for the same attempt.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): ServiceResult;
}
