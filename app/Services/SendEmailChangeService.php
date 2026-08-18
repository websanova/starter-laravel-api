<?php

namespace App\Services;

use App\Models\EmailChangeToken;
use App\Models\User;
use App\Notifications\EmailChangeNotification;
use App\Support\ServiceResult;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SendEmailChangeService
{
    /**
     * Create a token and send a confirmation email to the new address.
     */
    public function handle(User $user, string $email): ServiceResult
    {
        if ($this->isThrottled($user)) {
            return ServiceResult::error('email_change.throttled');
        }

        EmailChangeToken::where('user_id', $user->id)->delete();

        $token = Str::random(64);

        EmailChangeToken::create([
            'user_id' => $user->id,
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $user->notify(new EmailChangeNotification($token, $email));

        return ServiceResult::success();
    }

    /**
     * Check if the user is throttled from requesting another change.
     */
    protected function isThrottled(User $user): bool
    {
        $latest = EmailChangeToken::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        if (!$latest) {
            return false;
        }

        return $latest->created_at->diffInSeconds(now()) < config('auth.email_change.throttle', 60);
    }
}
