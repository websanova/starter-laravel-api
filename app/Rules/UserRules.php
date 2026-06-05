<?php

namespace App\Rules;

use App\Enums\UserSort;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRules
{
    /**
     * Validation rules for the sort_by field.
     */
    public static function sortBy(): array
    {
        return ['sometimes', 'string', Rule::enum(UserSort::class)];
    }

    /**
     * Validation rules for the first_name field.
     */
    public static function firstName(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:255',
        ];
    }

    /**
     * Validation rules for the last_name field.
     */
    public static function lastName(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'max:255',
        ];
    }

    /**
     * Validation rules for the email field.
     */
    public static function email(bool $required = true, ?int $ignore = null): array
    {
        $unique = $ignore
            ? 'unique:users,email,' . $ignore
            : 'unique:users';

        return [
            $required ? 'required' : 'sometimes',
            'string',
            'email',
            'max:255',
            $unique,
        ];
    }

    /**
     * Validation rules for the password field.
     */
    public static function password(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'confirmed',
            Password::defaults(),
        ];
    }

    /**
     * Validation rules for the avatar field.
     */
    public static function avatar(): array
    {
        return [
            'required',
            'image',
            'mimes:jpeg,png,webp',
            'max:2048',
        ];
    }
}
