<?php

namespace App\Rules;

class PriceRules
{
    /**
     * Validation rules for the Stripe price lookup key field.
     */
    public static function lookupKey(): array
    {
        return ['required', 'string', 'max:255'];
    }
}
