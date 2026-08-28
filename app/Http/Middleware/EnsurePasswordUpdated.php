<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordUpdated
{
    /**
     * Block all requests until the user has updated their password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->is_password_reset_required) {
            return response()->json([
                'error' => 'password_reset_required',
                'message' => __('responses.auth.password_reset_required'),
            ], 403);
        }

        return $next($request);
    }
}
