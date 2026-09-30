<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface UpdatePaymentMethodAddressProvider
{
    /**
     * Write the address a card is collected against to the provider's customer.
     * Nothing is stored locally, the customer holds it and every renewal invoice
     * computes tax off it.
     *
     * @param  array<string, string|null>  $address
     */
    public function handle(User $user, array $address): ServiceResult;
}
