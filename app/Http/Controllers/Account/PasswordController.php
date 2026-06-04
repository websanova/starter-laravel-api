<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Password\UpdateRequest;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Update the authenticated user's password.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
            'is_password_reset_required' => false,
        ]);

        $user->notify(new PasswordChangedNotification());

        return response()->json(['message' => __('responses.password.updated')]);
    }
}
