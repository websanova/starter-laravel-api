<?php

namespace App\Models\Concerns;

use App\Enums\VerificationChannel;
use App\Enums\VerificationMode;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait ManagesVerification
{
    /**
     * Determine whether the user must verify their email before accessing
     * protected routes.
     */
    protected function isEmailVerificationRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->channelVerificationRequired(VerificationChannel::Email),
        );
    }

    /**
     * Determine whether the user must verify their phone before accessing
     * protected routes.
     */
    protected function isPhoneVerificationRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->channelVerificationRequired(VerificationChannel::Phone),
        );
    }

    /**
     * Determine whether the user must verify any channel before accessing
     * protected routes.
     */
    protected function isVerificationRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => collect(VerificationChannel::cases())
                ->contains(fn (VerificationChannel $channel) => $this->channelVerificationRequired($channel)),
        );
    }

    /**
     * Determine whether any channel still needs to be verified, ignoring
     * the grace period.
     */
    public function hasPendingVerification(): bool
    {
        return collect(VerificationChannel::cases())
            ->contains(fn (VerificationChannel $channel) => $this->channelVerificationPending($channel));
    }

    /**
     * Determine whether the given channel needs to be verified. A channel
     * is pending when its mode is required, the user has the identifier,
     * and it has not been verified yet.
     */
    protected function channelVerificationPending(VerificationChannel $channel): bool
    {
        return config("verification.mode.{$channel->value}") === VerificationMode::Required
            && $this->{$channel->field()}
            && !$this->{$channel->column()};
    }

    /**
     * Determine whether the given channel blocks access to protected
     * routes, taking the channel's grace period into account.
     */
    protected function channelVerificationRequired(VerificationChannel $channel): bool
    {
        if (!$this->channelVerificationPending($channel)) {
            return false;
        }

        $gracePeriod = config("verification.grace_period.{$channel->value}");

        return !($gracePeriod && $this->created_at->diffInSeconds(now()) < $gracePeriod);
    }
}
