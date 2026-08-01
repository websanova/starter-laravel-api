<?php

namespace App\Http\Controllers\App;

use App\Contracts\SubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Subscription\StoreRequest;
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
     * Open a checkout session and return the secret the client mounts the
     * embedded checkout against. The subscription does not exist locally until
     * the provider's webhook reports it, so the client polls its profile after
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
