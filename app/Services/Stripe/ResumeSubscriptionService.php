<?php

namespace App\Services\Stripe;

use App\Contracts\ResumeSubscriptionProvider;
use App\Models\User;
use Laravel\Cashier\Subscription;

class ResumeSubscriptionService implements ResumeSubscriptionProvider
{
    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function handle(User $user): Subscription
    {
        $user->subscription()->resume();

        return $user->subscription();
    }
}
