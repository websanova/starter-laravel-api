<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Subscription\DestroyRequest;
use App\Http\Requests\Account\Subscription\ResumeRequest;
use App\Http\Requests\Account\Subscription\UpdateRequest;
use App\Http\Resources\Account\SubscriptionResource;
use App\Models\Plan;
use App\Services\PromotionCodeService;
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
     * Create or swap a subscription.
     */
    public function update(UpdateRequest $request, PromotionCodeService $promotionCodeService): JsonResponse
    {
        $user = $request->user();
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        if ($user->subscribed()) {
            $subscription = $user->swapPlan($plan, $request->validated('interval'));
        } else {
            $promotionCodeId = null;

            if ($request->validated('promotion_code')) {
                $result = $promotionCodeService->resolve($request->validated('promotion_code'));

                if (!$result->success) {
                    throw ValidationException::withMessages([
                        'promotion_code' => [__("validation.{$result->error}")],
                    ]);
                }

                $promotionCodeId = $result->data->id;
            }

            $subscription = $user->subscribeToPlan($plan, $request->validated('interval'), $promotionCodeId);
        }

        $response = [
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.subscription.updated'),
        ];

        if ($subscription->stripe_status === 'incomplete') {
            $response['client_secret'] = $subscription->latestPayment()->asStripePaymentIntent()->client_secret;
        }

        return response()->json($response);
    }

    /**
     * Cancel the subscription at period end.
     */
    public function destroy(DestroyRequest $request): JsonResponse
    {
        $request->user()->cancelPlan();

        return response()->json([
            'message' => __('responses.subscription.cancelled'),
        ]);
    }

    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function resume(ResumeRequest $request): JsonResponse
    {
        $subscription = $request->user()->resumePlan();

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.subscription.resumed'),
        ]);
    }
}
