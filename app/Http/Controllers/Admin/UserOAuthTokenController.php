<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserOAuthToken\DestroyRequest;
use App\Http\Requests\Admin\UserOAuthToken\IndexRequest;
use App\Http\Resources\Admin\OAuthTokenResource;
use App\Models\PassportToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserOAuthTokenController extends Controller
{
    /**
     * List a user's active OAuth access tokens.
     */
    public function index(IndexRequest $request, User $user): JsonResponse
    {
        $tokens = $user->oauthTokens()
            ->with('client')
            ->where('revoked', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->paginate($request->validated('per_page', 15));

        return response()->json(OAuthTokenResource::paginated($tokens));
    }

    /**
     * Revoke a user's OAuth access token. The refresh token is revoked with
     * it, otherwise the client could use it to get a new access token.
     */
    public function destroy(DestroyRequest $request, User $user, PassportToken $oauthToken): JsonResponse
    {
        $oauthToken->revoke();
        $oauthToken->refreshToken?->revoke();

        return response()->json(null, 204);
    }
}
