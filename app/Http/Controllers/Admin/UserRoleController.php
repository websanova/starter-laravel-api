<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRole\UpdateRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    /**
     * Update a user's role.
     */
    public function update(UpdateRequest $request, User $user): JsonResponse
    {
        $role = $request->validated('role');

        if ($role) {
            $user->syncRoles([$role]);
        } else {
            $user->syncRoles([]);
        }

        return response()->json([
            'data' => new UserResource($user->fresh()),
        ]);
    }
}
