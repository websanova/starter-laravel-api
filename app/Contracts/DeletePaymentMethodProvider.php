<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface DeletePaymentMethodProvider
{
    /**
     * Take the card on file off the customer, permanently. Only allowed once
     * nothing further is going to be billed, which the implementation decides
     * for itself rather than trusting what the client showed the user.
     */
    public function handle(User $user): ServiceResult;
}
