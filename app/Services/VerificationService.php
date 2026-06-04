<?php

namespace App\Services;

use App\Enums\VerificationMode;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerificationCodeNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VerificationService
{
    /**
     * Send a verification code to the user via all configured channels.
     */
    public function send(User $user): void
    {
        if (config('verification.mode') === VerificationMode::Disabled) {
            return;
        }

        $code = $this->generateCode();

        VerificationCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addSeconds(config('verification.code_expiry')),
        ]);

        $user->notify(new VerificationCodeNotification($code));
    }

    /**
     * Verify the code for the given user.
     */
    public function verify(User $user, string $code): void
    {
        $record = VerificationCode::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', config('verification.max_attempts'))
            ->latest()
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'code' => [__('responses.verification.no_valid_code')],
            ]);
        }

        if (!Hash::check($code, $record->code)) {
            $record->increment('attempts');

            throw ValidationException::withMessages([
                'code' => [__('responses.verification.invalid_code')],
            ]);
        }

        $record->update(['verified_at' => now()]);
        $user->update(['email_verified_at' => now()]);
        $user->notify(new WelcomeNotification());
    }

    /**
     * Check if the user can request a new code (throttle check).
     */
    public function canResend(User $user): bool
    {
        $latest = VerificationCode::where('user_id', $user->id)
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
