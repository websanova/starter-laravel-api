<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRestore\UpdateRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserRestoreController extends Controller
{
    /**
     * Restore a soft-deleted user.
     */
    public function update(UpdateRequest $request, User $user): JsonResponse
    {
        $user->restore();

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
