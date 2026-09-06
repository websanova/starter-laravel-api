<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface ResumeSubscriptionProvider
{
    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function handle(User $user): ServiceResult;
}
