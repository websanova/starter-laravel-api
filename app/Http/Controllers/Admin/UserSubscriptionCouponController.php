<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserSubscriptionCoupon\DestroyRequest;
use App\Http\Requests\Admin\UserSubscriptionCoupon\StoreRequest;
use App\Http\Resources\Admin\SubscriptionResource;
use App\Models\User;
use App\Services\PromotionCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserSubscriptionCouponController extends Controller
{
    /**
     * Apply a promotion code to a user's existing subscription.
     */
    public function store(StoreRequest $request, User $user, PromotionCodeService $promotionCodeService): JsonResponse
    {
        $result = $promotionCodeService->resolve($request->validated('promotion_code'));

        if (!$result->success) {
            throw ValidationException::withMessages([
                'promotion_code' => [__("validation.{$result->error}")],
            ]);
        }

        $user->subscription()->applyPromotionCode($result->data->id);

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
