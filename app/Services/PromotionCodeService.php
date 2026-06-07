<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PromotionCodeService
{
    /**
     * Resolve a promotion code string to a Stripe promotion code object.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resolve(string $code): object
    {
        try {
            $stripe = new StripeClient(config('cashier.secret'));

            $promotionCodes = $stripe->promotionCodes->all([
                'code' => $code,
                'active' => true,
                'limit' => 1,
            ]);
        } catch (ApiErrorException) {
            throw $this->invalid();
        }

        if ($promotionCodes->data === []) {
            throw $this->invalid();
        }

        $promotionCode = $promotionCodes->data[0];

        if ($promotionCode->coupon && !$promotionCode->coupon->valid) {
            throw $this->expired();
        }

        if ($promotionCode->max_redemptions !== null && $promotionCode->times_redeemed >= $promotionCode->max_redemptions) {
            throw $this->maxRedemptions();
        }

        if ($promotionCode->expires_at !== null && $promotionCode->expires_at < time()) {
            throw $this->expired();
        }

        return $promotionCode;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function invalid(): ValidationException
    {
        return ValidationException::withMessages([
            'promotion_code' => [__('validation.promotion_code.invalid')],
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function expired(): ValidationException
    {
        return ValidationException::withMessages([
            'promotion_code' => [__('validation.promotion_code.expired')],
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function maxRedemptions(): ValidationException
    {
        return ValidationException::withMessages([
            'promotion_code' => [__('validation.promotion_code.max_redemptions')],
        ]);
    }
}
