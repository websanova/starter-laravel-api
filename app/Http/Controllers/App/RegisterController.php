<?php

namespace App\Http\Controllers\App;

use App\Enums\VerificationChannel;
use App\Enums\VerificationMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Register\StoreRequest;
use App\Http\Resources\App\ProfileResource;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Register a new user and return a Sanctum token.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->validated('phone'),
            'password' => Hash::make($request->password),
            'locale' => $request->locale,
            'timezone' => $request->timezone,
        ]);

        foreach (VerificationChannel::cases() as $channel) {
            if (config("verification.mode.{$channel->value}") === VerificationMode::Auto && $user->{$channel->field()}) {
                $user->update([$channel->verifiedAtField() => now()]);
            }
        }

        $user->startTrial();

        // Under required mode the welcome is deferred until the email is
        // verified, and sent from ConfirmVerificationService instead.
        if (config('verification.mode.email') !== VerificationMode::Required) {
            $user->notify(new WelcomeNotification());
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => new ProfileResource($user->loadMissing(['plan.prices', 'subscriptions'])),
            'token' => $token,
        ], 201);
    }
}

