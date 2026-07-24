<?php

namespace App\Services;

use App\Enums\VerificationChannel;
use App\Enums\VerificationMode;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerificationCodeNotification;
use App\Support\ServiceResult;
use Illuminate\Support\Facades\Hash;

class VerificationService
{
    /**
     * Send a verification code for the given channel.
     */
    public function send(User $user, VerificationChannel $channel): void
    {
        if (config("verification.mode.{$channel->value}") === VerificationMode::Disabled) {
            return;
        }

        if (!$user->{$channel->field()}) {
            return;
        }

        $code = $this->generateCode();

        VerificationCode::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'code' => Hash::make($code),
            'expires_at' => now()->addSeconds(config('verification.code_expiry')),
        ]);

        $user->notify(new VerificationCodeNotification($channel, $code));
    }

    /**
     * Verify the code for the given user and channel.
     */
    public function verify(User $user, string $code, VerificationChannel $channel): ServiceResult
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

        $record->update(['verified_at' => now()]);
        $user->update([$channel->verifiedAtField() => now()]);

        return ServiceResult::success();
    }

    /**
     * Check if the user can request a new code for the given channel
     * (throttle check).
     */
    public function canResend(User $user, VerificationChannel $channel): bool
    {
        $latest = VerificationCode::where('user_id', $user->id)
            ->where('channel', $channel)
            ->latest()
            ->first();

        if (!$latest) {
            return true;
        }

        return $latest->created_at->diffInSeconds(now()) >= config('verification.resend_throttle');
    }

    /**
     * Generate a random numeric code.
     */
    protected function generateCode(): string
    {
        $length = config('verification.code_length');

        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

}
