<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\IndexRequest;
use App\Http\Requests\Admin\User\ShowRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Requests\Admin\User\DestroyRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * List users with optional search and filtering.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $users = User::query()
            ->with(['plan.prices', 'roles', 'subscriptions'])
            ->forSearch($request->validated('search'))
            ->forRole($request->validated('role'))
            ->forTrashed($request->validated('trashed'))
            ->sortBy($request->validated('sort_by'), $request->validated('sort_dir'))
            ->paginate($request->validated('per_page', 15));

        return response()->json(UserResource::collection($users)->response()->getData(true));
    }

    /**
     * Show a single user.
     */
    public function show(ShowRequest $request, User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user->load(['plan.prices', 'roles', 'subscriptions'])),
        ]);
    }

    /**
     * Update a user's name fields.
     */
    public function update(UpdateRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return response()->json([
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Soft delete a user and revoke their tokens.
     */
    public function destroy(DestroyRequest $request, User $user): JsonResponse
    {
        $user->tokens()->delete();
        $user->delete();

        return response()->json(null, 204);
    }
}
