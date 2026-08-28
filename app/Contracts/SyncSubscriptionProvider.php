<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface SyncSubscriptionProvider
{
    /**
     * Commit every local row a completed checkout session accounts for, the
     * subscription, the address and the card. The entry point for both the
     * client reporting back and the webhook that backs it up, so it has to be
     * safe to run twice.
     */
    public function handle(User $user, string $sessionId): ServiceResult;
}
