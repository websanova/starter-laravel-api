<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Ensure the authenticated user has an admin or super role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!$user->hasRole(UserRole::Admin) && !$user->hasRole(UserRole::Super))) {
            return response()->json([
                'message' => __('responses.auth.forbidden'),
            ], 403);
        }

        return $next($request);
    }
}
