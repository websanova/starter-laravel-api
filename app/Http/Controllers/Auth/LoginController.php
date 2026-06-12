<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\DestroyRequest;
use App\Http\Requests\Auth\Login\StoreRequest;
use App\Http\Requests\Auth\Login\UpdateRequest;
use App\Http\Resources\Account\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Authenticate a user and return a Sanctum token.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = User::withTrashed()->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('responses.auth.failed')],
            ]);
        }

        if ($user->trashed()) {
            $gracePeriod = config('auth.delete.grace_period');
            $deadline = $user->deleted_at->addDays($gracePeriod);

            if ($gracePeriod === 0 || Carbon::now()->greaterThan($deadline)) {
                throw ValidationException::withMessages([
                    'email' => [__('responses.auth.deleted')],
                ]);
            }

            $user->restore();
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user->loadMissing(['plan.prices', 'subscriptions'])),
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
            'data' => new UserResource($user->loadMissing(['plan.prices', 'subscriptions'])),
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
