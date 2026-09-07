<?php

namespace App\Http\Controllers\App;

use App\Contracts\ChangeSubscriptionPlanProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionUpdate\StoreRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionUpdateController extends Controller
{
    /**
     * Swap the subscription to a different plan or interval. What comes back is
     * the proration invoice's outcome, since the price change stands whether or
     * not it settled and an error would read as though nothing happened. The
     * subscription itself is not returned, the client refreshes the auth user
     * on every branch anyway.
     */
    public function store(StoreRequest $request, ChangeSubscriptionPlanProvider $changeSubscriptionPlan): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $result = $changeSubscriptionPlan->handle(
            $request->user(),
            $plan,
            $request->validated('interval'),
        );

        if (!$result->success) {
            return $this->error($result, 'subscription');
        }

        return response()->json([
            'data' => $result->data['payment'],
            'message' => __('responses.subscription.updated'),
        ]);
    }
}
