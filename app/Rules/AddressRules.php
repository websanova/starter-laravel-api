<?php

namespace App\Rules;

class AddressRules
{
    /**
     * Validation rules for the line1 field.
     */
    public static function line1(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Validation rules for the line2 field.
     */
    public static function line2(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Validation rules for the city field.
     */
    public static function city(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Validation rules for the postal_code field.
     */
    public static function postalCode(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:20',
        ];
    }

    /**
     * Validation rules for the state field. Free text rather than a per country
     * list, since the shape of a subdivision code is the provider's to police.
     */
    public static function state(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Validation rules for the country field. Two letter ISO 3166-1 alpha-2,
     * which is what billing providers expect. The list itself is not checked
     * here since the provider rejects anything that is not a real code, and
     * carrying a copy of it locally only creates something to keep in sync.
     */
    public static function country(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'size:2',
            'uppercase',
        ];
    }
}
