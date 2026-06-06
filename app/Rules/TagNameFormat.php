<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TagNameFormat implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[\pL\pN][\pL\pN \.\-\+\#]*$/u', $value)) {
            $fail(__('validation.tag_name_format'));
        }
    }
}
