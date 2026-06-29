<?php

namespace App\Http\Requests\App\Verification;

use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;

class VerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => SharedRules::verificationCode(),
        ];
    }
}

