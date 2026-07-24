<?php

namespace App\Enums;

enum VerificationChannel: string
{
    case Email = 'email';
    case Phone = 'phone';

    /**
     * The user column holding the identifier for this channel.
     */
    public function field(): string
    {
        return match ($this) {
            self::Email => 'email',
            self::Phone => 'phone',
        };
    }

    /**
     * The user column recording when this channel was verified.
     */
    public function column(): string
    {
        return match ($this) {
            self::Email => 'email_verified_at',
            self::Phone => 'phone_verified_at',
        };
    }
}
