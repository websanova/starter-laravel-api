<?php

namespace App\Rules;

use App\Enums\PlanInterval;
use Illuminate\Validation\Rule;

class SubscriptionRules
{
    /**
     * Validation rules for the promotion code field.
     */
    public static function promotionCode(bool $required = false): array
    {
        return array_merge(
            $required ? ['required'] : ['sometimes', 'nullable'],
            ['string', 'alpha_dash', 'max:50'],
        );
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

    /**
     * Validation rules for the billing interval field when optional.
     */
    public static function intervalOptional(): array
    {
        return ['sometimes', 'nullable', 'string', Rule::enum(PlanInterval::class)];
    }
}
