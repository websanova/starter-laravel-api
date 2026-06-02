<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

class UserRules
{
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
