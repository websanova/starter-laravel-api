<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserSanctumToken\DestroyRequest;
use App\Http\Requests\Admin\UserSanctumToken\IndexRequest;
use App\Http\Resources\Admin\SanctumTokenResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

class UserSanctumTokenController extends Controller
{
    /**
     * List a user's unexpired Sanctum tokens.
     */
    public function index(IndexRequest $request, User $user): JsonResponse
    {
        $tokens = $user->sanctumTokens()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->paginate($request->validated('per_page', 15));

        return response()->json(SanctumTokenResource::paginated($tokens));
    }

    /**
     * Delete a user's Sanctum token.
     */
    public function destroy(DestroyRequest $request, User $user, PersonalAccessToken $sanctumToken): JsonResponse
    {
        $sanctumToken->delete();

        return response()->json(null, 204);
    }
}
