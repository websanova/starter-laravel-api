<?php

namespace App\Services;

use App\Enums\VerificationChannel;
use App\Enums\VerificationMode;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\WelcomeNotification;
use App\Support\ServiceResult;
use Illuminate\Support\Facades\Hash;

class ConfirmVerificationService
{
    /**
     * Verify the code for the given user and channel.
     */
    public function handle(User $user, string $code, VerificationChannel $channel): ServiceResult
    {
        $record = VerificationCode::where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', config('verification.max_attempts'))
            ->latest()
            ->first();

        if (!$record) {
            return ServiceResult::error('verification.no_valid_code');
        }

        if (!Hash::check($code, $record->code)) {
            $record->increment('attempts');

            return ServiceResult::error('verification.invalid_code');
        }

        $alreadyVerified = (bool) $user->{$channel->verifiedAtField()};

        $record->update(['verified_at' => now()]);
        $user->update([$channel->verifiedAtField() => now()]);

        // Registration skips the welcome when email verification is required,
        // so it is sent here on the first successful email verification.
        if (
            $channel === VerificationChannel::Email
            && !$alreadyVerified
            && config('verification.mode.email') === VerificationMode::Required
        ) {
            $user->notify(new WelcomeNotification());
        }

        return ServiceResult::success();
    }
}
