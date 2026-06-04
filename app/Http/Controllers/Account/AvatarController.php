<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Avatar\DestroyRequest;
use App\Http\Requests\Account\Avatar\StoreRequest;
use App\Http\Resources\Account\UserResource;
use Illuminate\Http\JsonResponse;

class AvatarController extends Controller
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
        $user = $request->user();
        $user->deleteAvatar();

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
