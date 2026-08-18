<?php

namespace App\Contracts;

use App\Models\User;

interface ResumeSubscriptionProvider
{
    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function handle(User $user): mixed;
}
