<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    /**
     * Update the authenticated user's last_active_at timestamp.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $throttle = config('auth.activity_throttle');

            if (!$user->last_active_at || $user->last_active_at->diffInSeconds(now()) >= $throttle) {
                $user->updateQuietly(['last_active_at' => now()]);
            }
        }

        return $next($request);
    }
}
