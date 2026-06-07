<?php

namespace App\Rules;

use App\Enums\SortDirection;
use App\Enums\TrashedFilter;
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
     * Validation rules for the search field.
     */
    public static function search(): array
    {
        return ['sometimes', 'string', 'max:255'];
    }

    /**
     * Validation rules for the sort_dir field.
     */
    public static function sortDir(): array
    {
        return ['sometimes', 'string', Rule::enum(SortDirection::class)];
    }

    /**
     * Validation rules for a token field.
     */
    public static function token(): array
    {
        return ['required', 'string'];
    }

    /**
     * Validation rules for the trashed filter field.
     */
    public static function trashed(): array
    {
        return ['sometimes', 'string', Rule::enum(TrashedFilter::class)];
    }

    /**
     * Validation rules for the verification code field.
     */
    public static function verificationCode(): array
    {
        return ['required', 'string', 'size:' . config('verification.code_length')];
    }
}
