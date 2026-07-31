<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Subscription\StoreRequest;
use App\Http\Requests\App\Subscription\UpdateRequest;
use App\Http\Resources\App\SubscriptionResource;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
     * Start a subscription and return the Stripe intent the client confirms
     * against. The response carries nothing beyond what the payment element
     * needs, since the subscription is not real until the payment clears and
     * the client picks the resulting state up from the sync endpoint.
     */
    public function store(StoreRequest $request, SubscriptionService $subscriptions): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $result = $subscriptions->start(
            $request->user(),
            $plan,
            $request->validated('interval'),
            $request->validated('promotion_code'),
        );

        if (!$result->success) {
            if (str_starts_with($result->error, 'promotion_code.')) {
                throw ValidationException::withMessages([
                    'promotion_code' => [__("validation.{$result->error}")],
                ]);
            }

            return response()->json([
                'message' => __("responses.subscription.{$result->error}"),
            ], 409);
        }

        return response()->json(['data' => $result->data]);
    }

    /**
     * Swap the subscription to a different plan or interval.
     */
    public function update(UpdateRequest $request, SubscriptionService $subscriptions): JsonResponse
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
