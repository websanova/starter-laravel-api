<?php

namespace App\Rules;

use App\Enums\SortDirection;
use Illuminate\Validation\Rule;

class SharedRules
{
    /**
     * Validation rules for an ID field.
     */
    public static function id(bool $allowZero = false): array
    {
        return ['sometimes', 'integer', $allowZero ? 'min:0' : 'min:1'];
    }

    /**
     * Validation rules for the per_page field.
     */
    public static function perPage(): array
    {
        return ['sometimes', 'integer', 'min:1', 'max:100'];
    }

    /**
     * Validation rules for the sort_dir field.
     */
    public static function sortDir(): array
    {
        return ['sometimes', 'string', Rule::enum(SortDirection::class)];
    }
}
