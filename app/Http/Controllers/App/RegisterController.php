<?php

namespace App\Http\Controllers\App;

use App\Enums\VerificationMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Register\StoreRequest;
use App\Http\Resources\App\ProfileResource;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(
        protected VerificationService $verificationService
    ) {}

    /**
     * Register a new user and return a Sanctum token.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'locale' => $request->locale,
        ]);

        $mode = config('verification.mode');

        match ($mode) {
            VerificationMode::Auto => $user->update(['email_verified_at' => now()]),
            VerificationMode::Required => $this->verificationService->send($user),
            VerificationMode::Disabled => null,
        };

        if ($mode !== VerificationMode::Required) {
            $user->notify(new WelcomeNotification());
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => new ProfileResource($user->loadMissing(['plan.prices', 'subscriptions'])),
            'token' => $token,
        ], 201);
    }
}

