<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\User;
use App\Support\ServiceResult;

interface SubscriptionProvider
{
    /**
     * Commit a billing address, pushing it to the provider before storing it
     * locally. Cancels any payment attempt still in flight, since a finalized
     * invoice never recalculates its tax against the new address.
     */
    public function updateBillingAddress(User $user, array $address): ServiceResult;

    /**
     * Open a payment session and return the secret the client mounts its own
     * payment form against. The subscription is created up front and sits
     * incomplete until the client confirms, so this has to be safe to call
     * repeatedly for the same attempt.
     */
    public function intent(User $user, Plan $plan, PlanInterval $interval): ServiceResult;

    /**
     * Pull the live subscription state from the provider and commit it locally.
     * The entry point for a provider whose webhooks do not commit on their own.
     */
    public function sync(User $user): void;

    /**
     * Commit the card details shown in the account, promoting the one that paid
     * for the subscription to the customer default.
     */
    public function commitPaymentMethod(User $user): void;

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
}
