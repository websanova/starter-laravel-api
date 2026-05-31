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
        $mode = config('verification.mode');

        if ($mode !== 'required') {
            return $next($request);
        }

        if (!$request->user()->email_verified_at) {
            return response()->json([
                'message' => 'Your email address is not verified.',
            ], 403);
        }

        return $next($request);
    }
}
