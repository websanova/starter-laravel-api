<?php

namespace App\Http\Controllers\App;

use App\Contracts\ChangeSubscriptionPlanProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubscriptionUpdate\StoreRequest;
use App\Http\Resources\App\SubscriptionResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionUpdateController extends Controller
{
    /**
     * Swap the subscription to a different plan or interval. The payment field
     * carries the proration invoice when it did not settle on its own, since
     * the price change stands either way and an error would read as though
     * nothing happened.
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
            'data' => new SubscriptionResource($result->data['subscription']),
            'payment' => $result->data['payment'],
            'message' => __('responses.subscription.updated'),
        ]);
    }
}
