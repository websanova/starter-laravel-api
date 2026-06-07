<?php

namespace App\Services;

use App\Models\EmailChangeToken;
use App\Models\User;
use App\Notifications\EmailChangeNotification;
use App\Support\ServiceResult;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmailChangeService
{
    /**
     * Create a token and send a confirmation email to the new address.
     */
    public function sendConfirmation(User $user, string $email): void
    {
        $this->deleteExistingTokens($user);

        $token = Str::random(64);

        EmailChangeToken::create([
            'user_id' => $user->id,
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $user->notify(new EmailChangeNotification($token, $email));
    }

    /**
     * Confirm the email change using the token.
     */
    public function confirm(string $email, string $token): ServiceResult
    {
        $record = EmailChangeToken::where('email', $email)
            ->where('created_at', '>', now()->subMinutes(config('auth.email_change.expire', 60)))
            ->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return ServiceResult::error('email_change.invalid_token');
        }

        $record->user->update([
            'email' => $record->email,
            'email_verified_at' => now(),
        ]);

        $this->deleteExistingTokens($record->user);

        return ServiceResult::success();
    }

    /**
     * Check if the user is throttled from requesting another change.
     */
    public function isThrottled(User $user): bool
    {
        $latest = EmailChangeToken::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        if (!$latest) {
            return false;
        }

        return $latest->created_at->diffInSeconds(now()) < config('auth.email_change.throttle', 60);
    }

    /**
     * Delete all existing tokens for the user.
     */
    protected function deleteExistingTokens(User $user): void
    {
        EmailChangeToken::where('user_id', $user->id)->delete();
    }
}
