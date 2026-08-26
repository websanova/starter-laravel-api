<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface UpdateBillingAddressProvider
{
    /**
     * Commit a billing address, pushing it to the provider before storing it
     * locally and creating the customer when there isn't one. Touches no
     * subscription. An attempt already in flight is reconciled where the
     * subscription is created, which is the only place that knows what the new
     * address means for it.
     */
    public function handle(User $user, array $address): ServiceResult;
}
