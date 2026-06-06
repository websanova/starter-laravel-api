<?php

namespace App\Rules;

class TagRules
{
    /**
     * Validation rules for the name field.
     */
    public static function name(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:50',
        ];
    }
}
