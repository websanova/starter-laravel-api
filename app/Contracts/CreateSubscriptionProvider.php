<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;

interface CreateSubscriptionProvider
{
    /**
     * Create the subscription against a customer that already carries an
     * address and a default payment method, charging the first invoice inside
     * the same call. Also the one place an attempt left over from an earlier
     * pass is reconciled, since it is the only step that knows what the plan,
     * interval and tax location the user is asking for now mean for it.
     */
    public function handle(User $user, Plan $plan, PlanInterval $interval, ?string $promotionCodeId = null): ServiceResult;
}
