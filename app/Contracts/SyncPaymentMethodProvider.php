<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface SyncPaymentMethodProvider
{
    /**
     * Pick up a card the client confirmed but never reported back, resolving it
     * from the provider rather than anything the client still holds, and make
     * it the default everywhere it needs to be.
     */
    public function handle(User $user): ServiceResult;
}
