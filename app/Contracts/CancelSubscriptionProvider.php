<?php

namespace App\Contracts;

use App\Models\User;

interface CancelSubscriptionProvider
{
    /**
     * Cancel the subscription at period end.
     */
    public function handle(User $user): void;
}
