<?php

namespace App\Services\Stripe;

use App\Contracts\CancelSubscriptionProvider;
use App\Models\User;

class CancelSubscriptionService implements CancelSubscriptionProvider
{
    /**
     * Cancel the subscription at period end.
     */
    public function handle(User $user): void
    {
        $user->subscription()->cancel();
    }
}
