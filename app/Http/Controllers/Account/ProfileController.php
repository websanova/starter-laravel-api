<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Profile\DestroyRequest;
use App\Http\Requests\Account\Profile\ShowRequest;
use App\Http\Requests\Account\Profile\UpdateRequest;
use App\Http\Resources\Account\UserResource;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user.
     */
    public function show(ShowRequest $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('subscriptions')),
        ]);
    }

    /**
     * Update the authenticated user.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Soft delete the authenticated user and revoke tokens.
     */
    public function destroy(DestroyRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json(null, 204);
    }
}
