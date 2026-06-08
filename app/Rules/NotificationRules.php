<?php

namespace App\Rules;

class NotificationRules
{
    /**
     * Validation rules for the read field.
     */
    public static function read(bool $required = false): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'boolean',
        ];
    }
}
