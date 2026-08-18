<?php

namespace App\Http\Requests\App\VerificationResend;

use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'channel' => SharedRules::verificationChannel(),
        ];
    }
}

