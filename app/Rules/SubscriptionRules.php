<?php

namespace App\Rules;

use App\Enums\PlanInterval;
use App\Rules\ValidPromotionCode;
use Illuminate\Validation\Rule;

class SubscriptionRules
{
    /**
     * Validation rules for the optional promotion code field.
     */
    public static function promotionCode(): array
    {
        return ['sometimes', 'nullable', 'string', new ValidPromotionCode];
    }

    /**
     * Validation rules for the required promotion code field.
     */
    public static function promotionCodeRequired(): array
    {
        return ['required', 'string', new ValidPromotionCode];
    }

    /**
     * Validation rules for the plan slug field.
     */
    public static function plan(): array
    {
        return ['required', 'string', 'exists:plans,slug'];
    }

    /**
     * Validation rules for the billing interval field.
     */
    public static function interval(): array
    {
        return ['required', 'string', Rule::enum(PlanInterval::class)];
    }
}
