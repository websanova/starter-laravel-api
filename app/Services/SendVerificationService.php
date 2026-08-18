<?php

namespace App\Services;

use App\Enums\VerificationChannel;
use App\Enums\VerificationMode;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerificationCodeNotification;
use App\Support\ServiceResult;
use Illuminate\Support\Facades\Hash;

class SendVerificationService
{
    /**
     * Send a verification code for the given channel.
     */
    public function handle(User $user, VerificationChannel $channel): ServiceResult
    {
        if (!$this->canResend($user, $channel)) {
            return ServiceResult::error('verification.throttled');
        }

        if (config("verification.mode.{$channel->value}") === VerificationMode::Disabled) {
            return ServiceResult::success();
        }

        if (!$user->{$channel->field()}) {
            return ServiceResult::success();
        }

        $code = $this->generateCode();

        VerificationCode::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'code' => Hash::make($code),
            'expires_at' => now()->addSeconds(config('verification.code_expiry')),
        ]);

        $user->notify(new VerificationCodeNotification($channel, $code));

        return ServiceResult::success();
    }

    /**
     * Check if the user can request a new code for the given channel
     * (throttle check).
     */
    protected function canResend(User $user, VerificationChannel $channel): bool
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
