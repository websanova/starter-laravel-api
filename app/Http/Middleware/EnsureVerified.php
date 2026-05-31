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

        $user = $request->user();

        if (!$user->email_verified_at) {
            $gracePeriod = config('verification.grace_period');

            if ($gracePeriod && $user->created_at->diffInSeconds(now()) < $gracePeriod) {
                return $next($request);
            }

            return response()->json([
                'message' => 'Your email address is not verified.',
            ], 403);
        }

        return $next($request);
    }
}
