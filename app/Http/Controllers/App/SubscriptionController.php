<?php

namespace App\Http\Controllers\App;

use App\Contracts\SubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Subscription\UpdateRequest;
use App\Http\Resources\App\SubscriptionResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Show the user's current subscription.
     */
    public function show(): JsonResponse
    {
        $subscription = request()->user()->subscription();

        if (!$subscription) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Swap the subscription to a different plan or interval.
     */
    public function update(UpdateRequest $request, SubscriptionProvider $subscriptions): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $subscription = $subscriptions->swap(
            $request->user(),
            $plan,
            $request->validated('interval'),
        );

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.subscription.updated'),
        ]);
    }
}
