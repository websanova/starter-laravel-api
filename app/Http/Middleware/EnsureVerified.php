<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    /**
     * Ensure the authenticated user has verified their email.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->is_verification_required) {
            return response()->json([
                'error' => 'unverified',
                'message' => __('responses.auth.unverified'),
            ], 403);
        }

        return $next($request);
    }
}
