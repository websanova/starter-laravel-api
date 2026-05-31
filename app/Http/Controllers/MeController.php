<?php

namespace App\Http\Controllers;

use App\Http\Requests\Me\DestroyRequest;
use App\Http\Requests\Me\ShowRequest;
use App\Http\Requests\Me\UpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class MeController extends Controller
{
    /**
     * Show the authenticated user.
     */
    public function show(ShowRequest $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
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
