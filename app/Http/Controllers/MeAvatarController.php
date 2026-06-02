<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeAvatar\DestroyRequest;
use App\Http\Requests\MeAvatar\StoreRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class MeAvatarController extends Controller
{
    /**
     * Upload or replace the authenticated user's avatar.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->storeAvatar($request->file('avatar'));

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the authenticated user's avatar.
     */
    public function destroy(DestroyRequest $request): JsonResponse
    {
        $request->user()->deleteAvatar();

        return response()->json(null, 204);
    }
}
