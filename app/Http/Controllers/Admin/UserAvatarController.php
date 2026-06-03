<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserAvatar\DestroyRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserAvatarController extends Controller
{
    /**
     * Remove a user's avatar.
     */
    public function destroy(DestroyRequest $request, User $user): JsonResponse
    {
        $user->deleteAvatar();

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
