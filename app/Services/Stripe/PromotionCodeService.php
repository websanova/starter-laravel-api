<?php

namespace App\Services\Stripe;

use App\Contracts\PromotionCodeProvider;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class PromotionCodeService implements PromotionCodeProvider
{
    /**
     * Resolve a promotion code string to a Stripe promotion code object.
     */
    public function resolve(string $code): ServiceResult
    {
        try {
            $promotionCodes = Cashier::stripe()->promotionCodes->all([
                'code' => $code,
                'active' => true,
                'limit' => 1,
            ]);
        } catch (ApiErrorException) {
            return ServiceResult::error('promotion_code.invalid');
        }

        if ($promotionCodes->data === []) {
            return ServiceResult::error('promotion_code.invalid');
        }

        $promotionCode = $promotionCodes->data[0];

        if ($promotionCode->coupon && !$promotionCode->coupon->valid) {
            return ServiceResult::error('promotion_code.expired');
        }

        if ($promotionCode->max_redemptions !== null && $promotionCode->times_redeemed >= $promotionCode->max_redemptions) {
            return ServiceResult::error('promotion_code.max_redemptions');
        }

        if ($promotionCode->expires_at !== null && $promotionCode->expires_at < time()) {
            return ServiceResult::error('promotion_code.expired');
        }

        return ServiceResult::success($promotionCode);
    }
}
