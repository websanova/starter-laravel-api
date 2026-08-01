<?php

namespace App\Http\Controllers\App;

use App\Contracts\SubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionCheckout\StoreRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionCheckoutController extends Controller
{
    /**
     * Open a checkout session and return the secret the client mounts the
     * embedded checkout against. No subscription exists locally until the
     * provider's webhook reports it, so the client polls its profile once
     * checkout completes rather than reading state back from here.
     */
    public function store(StoreRequest $request, SubscriptionProvider $subscriptions): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $result = $subscriptions->start(
            $request->user(),
            $plan,
            $request->validated('interval'),
        );

        if (!$result->success) {
            return response()->json([
                'message' => __("responses.subscription.{$result->error}"),
            ], 409);
        }

        return response()->json(['data' => $result->data]);
    }
}
