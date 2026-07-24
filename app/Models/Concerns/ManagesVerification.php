<?php

namespace App\Models\Concerns;

use App\Enums\VerificationChannel;
use App\Enums\VerificationMode;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait ManagesVerification
{
    /**
     * The channels currently blocking access to protected routes, taking
     * each channel's grace period into account.
     */
    protected function verificationRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => collect(VerificationChannel::cases())
                ->filter(fn (VerificationChannel $channel) => $this->channelVerificationRequired($channel))
                ->map(fn (VerificationChannel $channel) => $channel->value)
                ->values()
                ->all(),
        );
    }

    /**
     * The channels that still need to be verified, ignoring the grace
     * period.
     */
    protected function verificationPending(): Attribute
    {
        return Attribute::make(
            get: fn () => collect(VerificationChannel::cases())
                ->filter(fn (VerificationChannel $channel) => $this->channelVerificationPending($channel))
                ->map(fn (VerificationChannel $channel) => $channel->value)
                ->values()
                ->all(),
        );
    }

    /**
     * Determine whether any channel is currently blocking access to
     * protected routes.
     */
    protected function isVerificationRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => !empty($this->verification_required),
        );
    }

    /**
     * Determine whether any channel still needs to be verified, ignoring
     * the grace period.
     */
    protected function isVerificationPending(): Attribute
    {
        return Attribute::make(
            get: fn () => !empty($this->verification_pending),
        );
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
            && !$this->{$channel->verifiedAtField()};
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
