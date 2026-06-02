<?php

namespace App\Http\Controllers;

use App\Enums\VerificationMode;
use App\Http\Requests\Register\StoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
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
        ]);

        $mode = config('verification.mode');

        match ($mode) {
            VerificationMode::Auto => $user->update(['email_verified_at' => now()]),
            VerificationMode::Required => $this->verificationService->send($user),
            VerificationMode::Disabled => null,
        };

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'token' => $token,
        ], 201);
    }
}
