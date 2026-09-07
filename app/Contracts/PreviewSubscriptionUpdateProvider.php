<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;

interface PreviewSubscriptionUpdateProvider
{
    /**
     * Quote what changing to the given plan and interval costs.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval): ServiceResult;
}
