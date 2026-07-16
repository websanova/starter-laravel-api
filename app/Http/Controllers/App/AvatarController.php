<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Avatar\DestroyRequest;
use App\Http\Requests\App\Avatar\StoreRequest;
use App\Http\Resources\App\AvatarResource;
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
            'data' => new AvatarResource($user),
        ]);
    }

    /**
     * Remove the authenticated user's avatar.
     */
    public function destroy(DestroyRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->deleteAvatar();

        return response()->json(null, 204);
    }
}
