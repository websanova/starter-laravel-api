<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Enums\UserSort;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRules
{
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

    /**
     * Validation rules for the email field.
     */
    public static function email(): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
        ];
    }

    /**
     * Validation rules for the email field when setting a new email.
     */
    public static function emailNew(?int $ignore = null): array
    {
        $unique = $ignore
            ? 'unique:users,email,' . $ignore
            : 'unique:users';

        return [
            ...self::email(),
            $unique,
        ];
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
     * Validation rules for the locale field.
     */
    public static function locale(): array
    {
        return ['sometimes', 'nullable', 'string', Rule::in(config('app.supported_locales'))];
    }

    /**
     * Validation rules for the timezone field.
     */
    public static function timezone(): array
    {
        return ['sometimes', 'nullable', 'timezone'];
    }

    /**
     * Validation rules for the password field.
     */
    public static function password(): array
    {
        return [
            'required',
            'string',
        ];
    }

    /**
     * Validation rules for the current_password field.
     */
    public static function passwordCurrent(): array
    {
        return [
            'required',
            'string',
            'current_password',
        ];
    }

    /**
     * Validation rules for the password field when setting a new password.
     */
    public static function passwordNew(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            Password::defaults(),
        ];
    }

    /**
     * Validation rules for the role field as an array item.
     */
    public static function role(): array
    {
        return ['string', Rule::enum(UserRole::class)];
    }

    /**
     * Validation rules for the role field when updating a user's role.
     */
    public static function roleUpdate(): array
    {
        return ['nullable', 'string', Rule::in(array_column(UserRole::cases(), 'value'))];
    }

    /**
     * Validation rules for the sort_by field.
     */
    public static function sortBy(): array
    {
        return ['sometimes', 'string', Rule::enum(UserSort::class)];
    }
}
