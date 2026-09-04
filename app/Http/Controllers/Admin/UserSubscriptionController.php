<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\CancelSubscriptionProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserSubscription\DestroyRequest;
use App\Http\Requests\Admin\UserSubscription\ShowRequest;
use App\Http\Resources\Admin\SubscriptionResource;
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
     * Cancel a user's subscription at period end.
     */
    public function destroy(DestroyRequest $request, User $user, CancelSubscriptionProvider $subscriptions): JsonResponse
    {
        $result = $subscriptions->handle($user);

        if (!$result->success) {
            return $this->error($result, 'subscription');
        }

        return response()->json([
            'message' => __('responses.admin.user.subscription_cancelled'),
        ]);
    }
}
