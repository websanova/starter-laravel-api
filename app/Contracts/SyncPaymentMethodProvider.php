<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface SyncPaymentMethodProvider
{
    /**
     * Put the card from a setup the client just confirmed in place as the
     * default everywhere it needs to be, without waiting on the webhook. The
     * setup reference comes from the client and is verified against the
     * customer before anything is read off it.
     */
    public function handle(User $user, string $setupIntentId): ServiceResult;
}
