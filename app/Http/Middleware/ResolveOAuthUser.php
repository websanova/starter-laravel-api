<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveOAuthUser
{
    /**
     * Swap the OAuthUser Passport authenticated for the real User so the rest of
     * the stack (verified, admin, tools) works with the same model as the API.
     * A soft deleted account is not found here and gets a 401.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::find($request->user()->getAuthIdentifier());

        if (!$user) {
            throw new AuthenticationException();
        }

        Auth::guard('api')->setUser($user);

        return $next($request);
    }
}
