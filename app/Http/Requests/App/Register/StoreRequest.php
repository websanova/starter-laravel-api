<?php

namespace App\Http\Requests\App\Register;

use App\Enums\VerificationMode;
use App\Rules\UserRules;
use App\Support\Timezone;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Silently discard the locale and timezone unless they are supported,
     * non-default values. An invalid or default value leaves the column
     * null so the user follows the configured default.
     */
    protected function prepareForValidation(): void
    {
        $locale = $this->input('locale');

        if (! in_array($locale, config('user.supported_locales'), true)
            || $locale === config('user.default_locale')) {
            $this->merge(['locale' => null]);
        }

        $timezone = $this->input('timezone');

        if (! in_array($timezone, Timezone::identifiers(), true)
            || $timezone === config('user.default_timezone')) {
            $this->merge(['timezone' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => UserRules::firstName(),
            'last_name' => UserRules::lastName(false),
            'email' => UserRules::emailNew(),
            'password' => UserRules::passwordNew(),
            'locale' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string'],
        ];

        if (config('verification.mode.phone') !== VerificationMode::Disabled) {
            $rules['phone'] = UserRules::phone(true);
        }

        return $rules;
    }
}

