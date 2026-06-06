<?php

namespace App\Http\Controllers\Account;

use App\Enums\PlanInterval;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Subscription\DestroyRequest;
use App\Http\Requests\Account\Subscription\ResumeRequest;
use App\Http\Requests\Account\Subscription\StoreRequest;
use App\Http\Requests\Account\Subscription\UpdateRequest;
use App\Http\Resources\Account\SubscriptionResource;
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
     * Subscribe to a plan.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));
        $priceId = $this->resolvePriceId($plan, $request->validated('interval'));

        $subscription = $user->newSubscription('default', $priceId);

        if (config('subscription.mode')->value === 'trial' && !$user->subscribed()) {
            $subscription->trialDays(config('subscription.trial_days'));
        }

        $subscription->create($user->defaultPaymentMethod()?->id);

        $user->update(['plan_id' => $plan->id]);

        return response()->json([
            'data' => new SubscriptionResource($user->subscription()),
            'message' => __('responses.subscription.created'),
        ], 201);
    }

    /**
     * Swap to a different plan.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));
        $priceId = $this->resolvePriceId($plan, $request->validated('interval'));

        $user->subscription()->swap($priceId);
        $user->update(['plan_id' => $plan->id]);

        return response()->json([
            'data' => new SubscriptionResource($user->subscription()),
            'message' => __('responses.subscription.updated'),
        ]);
    }

    /**
     * Cancel the subscription at period end.
     */
    public function destroy(DestroyRequest $request): JsonResponse
    {
        $request->user()->subscription()->cancel();

        return response()->json([
            'message' => __('responses.subscription.cancelled'),
        ]);
    }

    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function resume(ResumeRequest $request): JsonResponse
    {
        $request->user()->subscription()->resume();

        return response()->json([
            'data' => new SubscriptionResource($request->user()->subscription()),
            'message' => __('responses.subscription.resumed'),
        ]);
    }

    /**
     * Resolve the Stripe price ID for the given plan and interval.
     */
    private function resolvePriceId(Plan $plan, PlanInterval $interval): string
    {
        return match ($interval) {
            PlanInterval::Monthly => $plan->stripe_monthly_price_id,
            PlanInterval::Yearly => $plan->stripe_yearly_price_id,
        };
    }
}
