<?php

namespace App\Contracts;

use App\Models\User;

interface VerificationChannel
{
    /**
     * Send the verification code to the user via this channel.
     */
    public function send(User $user, string $code): void;
}
