<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;

interface SubscriptionProvider
{
    /**
     * Start a subscription and return whatever the client needs to complete
     * the payment with the provider. Nothing about the user's entitlement
     * changes until sync() commits it.
     */
    public function start(User $user, Plan $plan, PlanInterval $interval, ?string $promotionCode = null): ServiceResult;

    /**
     * Pull the live subscription state from the provider and commit it locally.
     * Runs from both the client after a completed payment and the provider's
     * webhook, so every write has to be idempotent.
     */
    public function sync(User $user): void;

    /**
     * Swap the subscription to a different plan or interval.
     */
    public function swap(User $user, Plan $plan, PlanInterval $interval): mixed;

    /**
     * Cancel the subscription at period end.
     */
    public function cancel(User $user): void;

    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function resume(User $user): mixed;

    /**
     * Assign a plan without billing, cancelling any active subscription.
     */
    public function assignComplimentary(User $user, Plan $plan): void;
}
