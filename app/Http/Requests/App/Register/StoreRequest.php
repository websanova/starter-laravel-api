<?php

namespace App\Http\Requests\App\Register;

use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Silently discard the locale unless it is a supported, non-default
     * value. An invalid or default locale leaves the column null so the
     * user follows the configured default.
     */
    protected function prepareForValidation(): void
    {
        $locale = $this->input('locale');

        if (! in_array($locale, config('user.supported_locales'), true)
            || $locale === config('user.default_locale')) {
            $this->merge(['locale' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => UserRules::firstName(false),
            'last_name' => UserRules::lastName(false),
            'email' => UserRules::emailNew(),
            'password' => UserRules::passwordNew(),
            'locale' => ['nullable', 'string'],
        ];
    }
}

