<?php

namespace App\Rules;

use App\Enums\PlanInterval;
use Illuminate\Validation\Rule;

class SubscriptionRules
{
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

    /**
     * Validation rules for the plan slug field (account side).
     */
    public static function planPublic(): array
    {
        return [
            'required',
            'string',
            Rule::exists('plans', 'slug')
                ->where('is_active', true)
                ->where('is_public', true)
                ->where(fn ($query) => $query->whereExists(fn ($sub) => $sub
                    ->from('prices')
                    ->whereColumn('prices.plan_id', 'plans.id')
                    ->whereNotNull('stripe_price_id'))),
        ];
    }

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
}
