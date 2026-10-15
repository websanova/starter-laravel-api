<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserAuthToken\DestroyRequest;
use App\Http\Requests\Admin\UserAuthToken\IndexRequest;
use App\Http\Resources\Admin\AuthTokenResource;
use App\Models\SanctumToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserAuthTokenController extends Controller
{
    /**
     * List a user's unexpired auth tokens.
     */
    public function index(IndexRequest $request, User $user): JsonResponse
    {
        $tokens = $user->authTokens()
            ->active()
            ->latest()
            ->paginate($request->validated('per_page', 15));

        return response()->json(AuthTokenResource::paginated($tokens));
    }

    /**
     * Delete a user's auth token.
     */
    public function destroy(DestroyRequest $request, User $user, SanctumToken $authToken): JsonResponse
    {
        $authToken->delete();

        return response()->json(null, 204);
    }
}
