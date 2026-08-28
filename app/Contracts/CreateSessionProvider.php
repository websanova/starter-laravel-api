<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;

interface CreateSessionProvider
{
    /**
     * Open a checkout session for the plan and hand back the secret the client
     * collects the address and card against. The subscription itself is created
     * by the provider when the user confirms, so nothing exists locally or at
     * the provider until that lands.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): ServiceResult;
}
