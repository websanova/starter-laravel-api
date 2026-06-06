<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserSubscription\DestroyRequest;
use App\Http\Requests\Admin\UserSubscription\ResumeRequest;
use App\Http\Requests\Admin\UserSubscription\ShowRequest;
use App\Http\Requests\Admin\UserSubscription\StoreRequest;
use App\Http\Requests\Admin\UserSubscription\UpdateRequest;
use App\Http\Resources\Admin\SubscriptionResource;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserSubscriptionController extends Controller
{
    /**
     * Show a user's current subscription.
     */
    public function show(ShowRequest $request, User $user): JsonResponse
    {
        $subscription = $user->subscription();

        if (!$subscription) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Subscribe a user to a plan.
     */
    public function store(StoreRequest $request, User $user): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $subscription = $user->subscribeToPlan($plan, $request->validated('interval'));

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.admin.user.subscription_created'),
        ], 201);
    }

    /**
     * Swap a user's plan.
     */
    public function update(UpdateRequest $request, User $user): JsonResponse
    {
        $plan = Plan::cached()->firstWhere('slug', $request->validated('plan'));

        $subscription = $user->swapPlan($plan, $request->validated('interval'));

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.admin.user.subscription_updated'),
        ]);
    }

    /**
     * Cancel a user's subscription at period end.
     */
    public function destroy(DestroyRequest $request, User $user): JsonResponse
    {
        $user->cancelPlan();

        return response()->json([
            'message' => __('responses.admin.user.subscription_cancelled'),
        ]);
    }

    /**
     * Resume a user's cancelled subscription.
     */
    public function resume(ResumeRequest $request, User $user): JsonResponse
    {
        $subscription = $user->resumePlan();

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => __('responses.admin.user.subscription_resumed'),
        ]);
    }
}
