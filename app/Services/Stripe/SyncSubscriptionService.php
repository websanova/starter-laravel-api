<?php

namespace App\Services\Stripe;

use App\Contracts\SyncSubscriptionProvider;
use App\Models\User;

class SyncSubscriptionService implements SyncSubscriptionProvider
{
    /**
     * Pull the live status from the provider and commit it locally. Called
     * after a challenge clears on the first invoice, where the row was written
     * before the challenge ran and still reads incomplete. The webhook commits
     * the same thing on its own, this is only what saves the user waiting for
     * it.
     */
    public function handle(User $user): void
    {
        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        if (!$subscription) {
            return;
        }

        $subscription->syncStripeStatus();

        /**
         * The cached plan is written by the webhook off the same status flip,
         * so without this the row reads active while the user is still holding
         * no plan until it lands.
         */
        $user->fillPlan()->save();
    }
}
