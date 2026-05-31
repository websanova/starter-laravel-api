<?php

namespace App\Services\Verification;

use App\Contracts\VerificationChannel;
use App\Models\User;
use App\Notifications\VerificationCodeNotification;

class EmailChannel implements VerificationChannel
{
    /**
     * Send the verification code to the user via email.
     */
    public function send(User $user, string $code): void
    {
        $user->notify(new VerificationCodeNotification($code));
    }
}
