<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserSubscriptionCoupon\DestroyRequest;
use App\Http\Requests\Admin\UserSubscriptionCoupon\StoreRequest;
use App\Http\Resources\Admin\SubscriptionResource;
use App\Models\User;
use App\Rules\ValidPromotionCode;
use Illuminate\Http\JsonResponse;

class UserSubscriptionCouponController extends Controller
{
    /**
     * Apply a promotion code to a user's existing subscription.
     */
    public function store(StoreRequest $request, User $user): JsonResponse
    {
        $promotionCodeId = ValidPromotionCode::resolved()->id;

        $user->subscription()->applyPromotionCode($promotionCodeId);

        return response()->json([
            'data' => new SubscriptionResource($user->subscription()),
            'message' => __('responses.admin.user.subscription_coupon_applied'),
        ]);
    }

    /**
     * Remove all discounts from a user's existing subscription.
     */
    public function destroy(DestroyRequest $request, User $user): JsonResponse
    {
        $user->subscription()->updateStripeSubscription([
            'discounts' => [],
        ]);

        return response()->json([
            'message' => __('responses.admin.user.subscription_coupon_removed'),
        ]);
    }
}
