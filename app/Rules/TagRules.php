<?php

namespace App\Rules;

use App\Enums\TagSort;
use Illuminate\Validation\Rule;

class TagRules
{
    /**
     * Validation rules for the sort_by field.
     */
    public static function sortBy(): array
    {
        return ['sometimes', 'string', Rule::enum(TagSort::class)];
    }

    /**
     * Validation rules for the name field.
     */
    public static function name(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:50',
            new TagNameFormat,
        ];
    }
}
