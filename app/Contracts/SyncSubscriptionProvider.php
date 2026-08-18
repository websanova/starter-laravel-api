<?php

namespace App\Contracts;

use App\Models\User;

interface SyncSubscriptionProvider
{
    /**
     * Pull the live subscription state from the provider and commit it locally.
     * The entry point for a provider whose webhooks do not commit on their own.
     */
    public function handle(User $user): void;
}
