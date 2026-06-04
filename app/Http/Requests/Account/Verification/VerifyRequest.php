<?php

namespace App\Http\Requests\Account\Verification;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:' . config('verification.code_length')],
        ];
    }
}
