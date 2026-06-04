<?php

namespace App\Rules;

class BookmarkRules
{
    /**
     * Validation rules for the url field.
     */
    public static function url(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'url',
            'max:2048',
        ];
    }

    /**
     * Validation rules for the title field.
     */
    public static function title(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:255',
        ];
    }

    /**
     * Validation rules for the description field.
     */
    public static function description(): array
    {
        return [
            'nullable',
            'string',
            'max:1000',
        ];
    }
}
