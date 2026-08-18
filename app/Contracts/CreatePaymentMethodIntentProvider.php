<?php

namespace App\Contracts;

use App\Models\User;
use App\Support\ServiceResult;

interface CreatePaymentMethodIntentProvider
{
    /**
     * Open a session for collecting a card and return the secret the client
     * mounts its own payment form against. Nothing is charged, so this only
     * ever hands back a setup secret.
     */
    public function handle(User $user): ServiceResult;
}
