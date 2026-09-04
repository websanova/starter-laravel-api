<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface CancelSubscriptionProvider
{
    /**
     * Cancel the subscription at period end.
     */
    public function handle(User $user): ServiceResult;
}
