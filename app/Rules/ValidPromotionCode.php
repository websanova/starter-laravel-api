<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ValidPromotionCode implements ValidationRule
{
    /**
     * The resolved Stripe promotion code object.
     */
    protected static ?object $resolved = null;

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        static::$resolved = null;

        try {
            $stripe = new StripeClient(config('cashier.secret'));

            $promotionCodes = $stripe->promotionCodes->all([
                'code' => $value,
                'active' => true,
                'limit' => 1,
            ]);

            if ($promotionCodes->data === []) {
                $fail(__('validation.promotion_code.invalid'));
                return;
            }

            $promotionCode = $promotionCodes->data[0];

            if ($promotionCode->coupon && !$promotionCode->coupon->valid) {
                $fail(__('validation.promotion_code.expired'));
                return;
            }

            if ($promotionCode->max_redemptions !== null && $promotionCode->times_redeemed >= $promotionCode->max_redemptions) {
                $fail(__('validation.promotion_code.max_redemptions'));
                return;
            }

            if ($promotionCode->expires_at !== null && $promotionCode->expires_at < time()) {
                $fail(__('validation.promotion_code.expired'));
                return;
            }

            static::$resolved = $promotionCode;
        } catch (ApiErrorException) {
            $fail(__('validation.promotion_code.invalid'));
        }
    }

    /**
     * Get the resolved Stripe promotion code from the last validation.
     */
    public static function resolved(): ?object
    {
        return static::$resolved;
    }
}
