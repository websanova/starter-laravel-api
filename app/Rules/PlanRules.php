<?php

namespace App\Rules;

use App\Enums\PlanSort;
use Illuminate\Validation\Rule;

class PlanRules
{
    /**
     * Validation rules for the features field.
     */
    public static function features(): array
    {
        return ['sometimes', 'array'];
    }

    /**
     * Validation rules for individual feature values.
     */
    public static function featureValue(): array
    {
        return ['nullable'];
    }

    /**
     * Validation rules for the is_active field.
     */
    public static function isActive(): array
    {
        return ['sometimes', 'boolean'];
    }

    /**
     * Validation rules for the is_public field.
     */
    public static function isPublic(): array
    {
        return ['sometimes', 'boolean'];
    }

    /**
     * Validation rules for the name field.
     */
    public static function name(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:255',
        ];
    }

    /**
     * Validation rules for the slug field.
     */
    public static function slug(bool $required = true, ?int $ignore = null): array
    {
        $unique = $ignore
            ? Rule::unique('plans', 'slug')->ignore($ignore)
            : 'unique:plans,slug';

        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:255',
            $unique,
        ];
    }

    /**
     * Validation rules for the sort_by field.
     */
    public static function sortBy(): array
    {
        return ['sometimes', 'string', Rule::enum(PlanSort::class)];
    }

    /**
     * Validation rules for the sort_order field.
     */
    public static function sortOrder(): array
    {
        return ['sometimes', 'integer', 'min:0'];
    }
}
