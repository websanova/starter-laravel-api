<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;

interface SubscriptionProvider
{
    /**
     * Open a checkout with the provider and return whatever the client needs
     * to mount it. Nothing is recorded locally, the provider creates the
     * subscription and the webhook commits it.
     */
    public function start(User $user, Plan $plan, PlanInterval $interval): ServiceResult;

    /**
     * Pull the live subscription state from the provider and commit it locally.
     * Driven by the provider's webhook, which can repeat an event at any time,
     * so every write has to be idempotent.
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
