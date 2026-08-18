<?php

namespace App\Services;

use App\Models\EmailChangeToken;
use App\Support\ServiceResult;
use Illuminate\Support\Facades\Hash;

class ConfirmEmailChangeService
{
    /**
     * Confirm the email change using the token.
     */
    public function handle(string $email, string $token): ServiceResult
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

        EmailChangeToken::where('user_id', $record->user->id)->delete();

        return ServiceResult::success();
    }
}
