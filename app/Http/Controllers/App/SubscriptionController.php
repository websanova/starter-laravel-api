<?php

namespace App\Http\Controllers\App;

use App\Contracts\ChangeSubscriptionPlanProvider;
use App\Contracts\CreateSubscriptionProvider;
use App\Contracts\ResolvePromotionCodeProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Subscription\StoreRequest;
use App\Http\Requests\App\Subscription\UpdateRequest;
use App\Http\Resources\App\SubscriptionResource;
use App\Models\Plan;
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
     * Create the subscription. Last step of the subscribe flow, so the customer
     * is expected to already carry a validated address and a default payment
     * method, and nothing about either is sent here.
     */
    public function store(StoreRequest $request, CreateSubscriptionProvider $subscriptions, ResolvePromotionCodeProvider $promotionCodes): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $promotionCodeId = null;

        if ($code = $request->validated('promotion_code')) {
            $promotionCode = $promotionCodes->handle($code);

            if (!$promotionCode->success) {
                throw ValidationException::withMessages([
                    'promotion_code' => [__("validation.{$promotionCode->error}")],
                ]);
            }

            $promotionCodeId = $promotionCode->data->id;
        }

        $result = $subscriptions->handle(
            $request->user(),
            $plan,
            $request->validated('interval'),
            $promotionCodeId,
        );

        if (!$result->success) {
            $response = ['message' => __("responses.subscription.{$result->error}")];

            if (config('app.debug') && isset($result->data['debug'])) {
                $response['debug'] = $result->data['debug'];
            }

            return response()->json($response, 409);
        }

        return response()->json([
            'data' => new SubscriptionResource($result->data['subscription']),
            'client_secret' => $result->data['client_secret'],
            'message' => __('responses.subscription.created'),
        ]);
    }

    /**
     * Swap the subscription to a different plan or interval.
     */
    public function update(UpdateRequest $request, ChangeSubscriptionPlanProvider $subscriptions): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $subscription = $subscriptions->handle(
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
