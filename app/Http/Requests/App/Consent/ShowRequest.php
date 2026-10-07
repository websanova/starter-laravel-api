<?php

namespace App\Http\Requests\App\Consent;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'response_type' => ['required', 'in:code'],
            'client_id' => ['required', 'string'],
            'redirect_uri' => ['required', 'string'],
            'scope' => ['required', 'string'],
            'state' => ['required', 'string'],
            'code_challenge' => ['required', 'string'],
            'code_challenge_method' => ['required', 'in:S256'],
        ];
    }
}
