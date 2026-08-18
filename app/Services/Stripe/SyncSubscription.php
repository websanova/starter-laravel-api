<?php

namespace App\Services\Stripe;

use App\Contracts\SyncSubscriptionProvider;
use App\Models\User;

class SyncSubscription implements SyncSubscriptionProvider
{
    /**
     * Pull the live status from the provider and commit it locally. Cashier's
     * own webhook handler writes the subscription row before anything here
     * runs, so nothing calls this today. It stays as the entry point for a
     * provider whose webhooks do not commit on their own.
     */
    public function handle(User $user): void
    {
        $subscription = $user->subscription();

        if (!$subscription) {
            return;
        }

        $subscription->syncStripeStatus();
    }
}
