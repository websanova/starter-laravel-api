<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserPasswordReset\StoreRequest;
use App\Models\User;
use App\Notifications\TempPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPasswordResetController extends Controller
{
    /**
     * Force a password reset for a user.
     */
    public function store(StoreRequest $request, User $user): JsonResponse
    {
        $tempPassword = Str::random(16);

        $user->update([
            'password' => Hash::make($tempPassword),
            'is_password_reset_required' => true,
        ]);

        $user->tokens()->delete();
        $user->notify(new TempPasswordNotification($tempPassword));

        return response()->json([
            'message' => __('responses.admin.user.password_reset'),
        ]);
    }
}
