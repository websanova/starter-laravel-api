<?php

namespace App\Rules;

use Illuminate\Validation\Rule;

class BookmarkRules
{
    /**
     * Validation rules for the category_id field.
     */
    public static function categoryId(int $userId): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('categories', 'id')->where('user_id', $userId),
        ];
    }


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
