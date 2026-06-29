<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\DestroyRequest;
use App\Http\Requests\Auth\Login\StoreRequest;
use App\Http\Requests\Auth\Login\UpdateRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthLoginController extends Controller
{
    /**
     * Authenticate an admin user and return a Sanctum token.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('responses.auth.failed')],
            ]);
        }

        if (! $user->hasRole(['super', 'admin'])) {
            throw ValidationException::withMessages([
                'email' => [__('responses.auth.failed')],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Refresh the current token (delete old, issue new).
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Revoke the current access token (logout).
     */
    public function destroy(DestroyRequest $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
