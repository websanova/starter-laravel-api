<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    /**
     * Ensure the authenticated user has access based on the subscription mode.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $user->loadMissing(['subscriptions', 'plan']);
        $mode = config('subscription.mode');

        $hasAccess = match ($mode) {
            SubscriptionMode::Freemium => true,
            SubscriptionMode::Trial, SubscriptionMode::Required => $user->is_complimentary || $user->is_on_trial || $user->is_subscribed,
        };

        if (!$hasAccess) {
            return response()->json([
                'message' => __('responses.subscription.required'),
            ], 403);
        }

        return $next($request);
    }
}
