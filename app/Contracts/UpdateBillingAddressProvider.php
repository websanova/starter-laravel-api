<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface UpdateBillingAddressProvider
{
    /**
     * Commit a billing address, pushing it to the provider before storing it
     * locally. Cancels any payment attempt still in flight, since a finalized
     * invoice never recalculates its tax against the new address.
     */
    public function handle(User $user, array $address): ServiceResult;
}
