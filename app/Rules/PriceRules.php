<?php

namespace App\Rules;

class PriceRules
{
    /**
     * Validation rules for the Stripe product ID field.
     */
    public static function stripeProductId(): array
    {
        return ['required', 'string', 'max:255'];
    }
}
